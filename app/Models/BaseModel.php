<?php
namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


abstract class BaseModel extends Model
{
use HasFactory;


/**
* Common guarded default: lock nothing during initial scaffolding.
* After generation, please tighten with $fillable per model.
*/
protected $guarded = [];


/**
* Default hidden keys you probably don't want in API responses
*/
protected $hidden = [
// 'password', 'remember_token'
];
}


// ------------------------------------------------------------
// File: app/Console/Commands/MakeModelsFromSchema.php
// Registers a command to generate Eloquent models for every table
// based on the current database schema.
// ------------------------------------------------------------
