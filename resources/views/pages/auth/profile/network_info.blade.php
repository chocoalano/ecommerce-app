@extends('layouts.app')
@section('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" rel="stylesheet" />
@endsection
@section('content')
    @php
        $customer = $customer ?? null;
    @endphp

    <section class="bg-gray-50 py-8 antialiased md:py-10">
        <div class="mx-auto max-w-7xl px-4 2xl:px-0">
            <div class="grid grid-cols-12 gap-6">
                @include('pages.auth.profile.partial.sidebar')

                <main class="col-span-12 md:col-span-9">
                    <div class="mb-6 flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-white shadow-sm">
                            <svg class="h-6 w-6 text-zinc-700" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 13h2v-2a7 7 0 0 1 14 0v2h2" />
                            </svg>
                        </span>
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">{{ $title }}</h1>
                            <p class="text-sm text-gray-500">Daftar jaringan anggota di bawah Anda.</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white rounded-md p-4">
                            <div id="myDiagramDiv" class="bg-white border border-gray-200 rounded-md w-full h-[550px]">
                            </div>
                            <div class="inline-flex rounded-md shadow-xs mt-10" role="group">
                                <button type="button" id="zoomToFit"
                                    class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-zinc-950 focus:z-10 focus:ring-2 focus:ring-zinc-900 focus:text-zinc-950 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-zinc-950 dark:focus:text-white">
                                    Zoom To Fit
                                </button>
                                <button type="button" id="centerRoot"
                                    class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-zinc-900 focus:z-10 focus:ring-2 focus:ring-zinc-950 focus:text-zinc-900 dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-zinc-950 dark:focus:text-white">
                                    Center On Root
                                </button>
                            </div>

                        </div>
                    </div>
                </main>
            </div>
        </div>
    </section>
@endsection
@section('js')
    <script src="https://cdn.jsdelivr.net/npm/gojs@3.1.0/release/go-debug.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>

    <script>
        const nameProperty = 'name';
        const genderProperty = 'gender';
        const statusProperty = 'status';
        const countProperty = 'count';

        const theme = {
            colors: {
                femaleBadgeBackground: '#FFCBEA',
                maleBadgeBackground: '#A2DAFF',
                femaleBadgeText: '#7A005E',
                maleBadgeText: '#001C76',
                kingQueenBorder: '#FEBA00',
                princePrincessBorder: '#679DDA',
                civilianBorder: '#58ADA7',
                personText: '#383838',
                personNodeBackground: '#FFFFFF',
                selectionStroke: '#485670',
                counterBackground: '#485670',
                counterBorder: '#FFFFFF',
                counterText: '#FFFFFF',
                link: '#686E76'
            },
            fonts: {
                badgeFont: 'bold 12px Poppins',
                birthDeathFont: '14px Poppins',
                nameFont: '500 18px Poppins',
                counterFont: '14px Poppins'
            }
        };

        // toggle highlight on mouse enter/leave
        // this sample also uses highlight for selection, so only unhighlight if unselected
        const onMouseEnterPart = (e, part) => part.isHighlighted = true;
        const onMouseLeavePart = (e, part) => { if (!part.isSelected) part.isHighlighted = false; }
        const onSelectionChange = part => { part.isHighlighted = part.isSelected; }

        const STROKE_WIDTH = 3;
        const ADORNMENT_STROKE_WIDTH = STROKE_WIDTH + 1;
        const CORNER_ROUNDNESS = 12;
        const IMAGE_TOP_MARGIN = 20;
        const MAIN_SHAPE_NAME = 'mainShape';
        const IMAGE_DIAMETER = 40;

        const getStrokeForStatus = status => {
            switch (status) {
                case 'king':
                case 'queen':
                    return theme.colors.kingQueenBorder;
                case 'prince':
                case 'princess':
                    return theme.colors.princePrincessBorder;
                case 'civilian':
                default:
                    return theme.colors.civilianBorder;
            }
        };

        function strokeStyle(shape) {
            shape.fill = theme.colors.personNodeBackground;
            shape.strokeWidth = STROKE_WIDTH;
            shape.bind('stroke', statusProperty, status => getStrokeForStatus(status));
            shape.bindObject('stroke', 'isHighlighted',
                (isHighlighted, obj) =>
                    isHighlighted
                        ? theme.colors.selectionStroke
                        : getStrokeForStatus(obj.part.data.status));
        }

        const personBadge = () =>
            new go.Panel('Auto', {
                alignmentFocus: go.Spot.TopRight,
                alignment: new go.Spot(1, 0, -25, STROKE_WIDTH - 0.5)
            })
                .add(
                    new go.Shape({
                        figure: 'RoundedRectangle',
                        parameter1: CORNER_ROUNDNESS,
                        parameter2: 4 | 8, // round only the bottom
                        desiredSize: new go.Size(NaN, 22.5),
                        stroke: null
                    }),
                    new go.TextBlock({
                        font: theme.fonts.badgeFont
                    })
                )

        const personBirthDeathTextBlock = () =>
            new go.TextBlock({
                stroke: theme.colors.personText,
                font: theme.fonts.birthDeathFont,
                alignmentFocus: go.Spot.Top,
                alignment: new go.Spot(0.5, 1, 0, -35)
            })
                .bind('text', '', ({ born, death }) => {
                    if (!born) return '';
                    return `${born} - ${death ?? ''}`;
                })

        // Panel to display the number of children a node has
        const personCounter = () =>
            new go.Panel('Auto', {
                visible: false,
                alignmentFocus: go.Spot.Center,
                alignment: go.Spot.Bottom
            })
                .bindObject('visible', '', obj => obj.findLinksOutOf().count > 0)
                .add(
                    new go.Shape('Circle', {
                        desiredSize: new go.Size(29, 29),
                        strokeWidth: STROKE_WIDTH,
                        stroke: theme.colors.counterBorder,
                        fill: theme.colors.counterBackground
                    }),
                    new go.TextBlock({
                        alignment: new go.Spot(0.5, 0.5, 0, 1),
                        stroke: theme.colors.counterText,
                        font: theme.fonts.counterFont,
                        textAlign: 'center'
                    })
                        .bindObject('text', '', obj => obj.findNodesOutOf().count)
                )

        const personImage = () =>
            new go.Panel('Spot', {
                alignmentFocus: go.Spot.Top,
                alignment: new go.Spot(0, 0, STROKE_WIDTH / 2, IMAGE_TOP_MARGIN)
            });

        const personMainShape = () =>
            new go.Shape({
                figure: 'RoundedRectangle',
                desiredSize: new go.Size(215, 110),
                portId: '',
                parameter1: CORNER_ROUNDNESS
            })
                .apply(strokeStyle);

        const personNameTextBlock = () =>
            new go.TextBlock({
                stroke: theme.colors.personText,
                font: theme.fonts.nameFont,
                desiredSize: new go.Size(160, 50),
                overflow: go.TextOverflow.Ellipsis,
                textAlign: 'center',
                verticalAlignment: go.Spot.Center,
                toolTip:
                    go.GraphObject.build('ToolTip')
                        .add(
                            new go.TextBlock({ margin: 4 })
                                .bind('text', nameProperty)
                        ),
                alignmentFocus: go.Spot.Top,
                alignment: new go.Spot(0.5, 0, 0, 25)
            })
                .bind('text', nameProperty)


        const createNodeTemplate = () =>
            new go.Node('Spot', {
                selectionAdorned: false,
                mouseEnter: onMouseEnterPart,
                mouseLeave: onMouseLeavePart,
                selectionChanged: onSelectionChange
            })
                .add(
                    new go.Panel('Spot')
                        .add(
                            personMainShape(),
                            personNameTextBlock(),
                            personBirthDeathTextBlock()
                        ),
                    personImage(),
                    personBadge(),
                    personCounter()
                )

        const createLinkTemplate = () =>
            new go.Link({
                selectionAdorned: false,
                routing: go.Routing.Orthogonal,
                layerName: 'Background',
                mouseEnter: onMouseEnterPart,
                mouseLeave: onMouseLeavePart
            })
                .add(
                    new go.Shape({
                        stroke: theme.colors.link,
                        strokeWidth: 1
                    })
                        .bindObject('stroke', 'isHighlighted',
                            isHighlighted => isHighlighted ? theme.colors.selectionStroke : theme.colors.link)
                        .bindObject('stroke', 'isSelected',
                            selected => selected ? theme.colors.selectionStroke : theme.colors.link)
                        .bindObject('strokeWidth', 'isSelected', selected => selected ? 2 : 1)
                );


        const initDiagram = divId => {
            const diagram = new go.Diagram(divId, {
                layout: new go.TreeLayout({
                    angle: 90,
                    nodeSpacing: 20,
                    layerSpacing: 50,
                    layerStyle: go.TreeLayout.LayerUniform,

                    // For compaction, make the last parents place their children in a bus
                    treeStyle: go.TreeStyle.LastParents,
                    alternateAngle: 90,
                    alternateLayerSpacing: 35,
                    alternateAlignment: go.TreeAlignment.BottomRightBus,
                    alternateNodeSpacing: 20
                }),
                'toolManager.hoverDelay': 100,
                linkTemplate: createLinkTemplate(),
                model: new go.TreeModel({ nodeKeyProperty: 'name' })
            });

            diagram.nodeTemplate = createNodeTemplate();
            const nodes = familyData;
            diagram.model.addNodeDataCollection(nodes);

            // Initially center on root:
            diagram.addDiagramListener('InitialLayoutCompleted', () => {
                const root = diagram.findNodeForKey('King George V');
                if (!root) return;
                diagram.scale = 0.6;
                diagram.scrollToRect(root.actualBounds);
            });

            // Setup zoom to fit button
            document.getElementById('zoomToFit').addEventListener('click', () => diagram.commandHandler.zoomToFit());

            document.getElementById('centerRoot').addEventListener('click', () => {
                diagram.scale = 1;
                diagram.commandHandler.scrollToPart(diagram.findNodeForKey('King George V'));
            });
        };

        const familyData = {!! json_encode($members) !!};


        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                initDiagram('myDiagramDiv');
            }, 300);
        });
    </script>

@endsection
