<?php
namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class File extends Eloquent {

    protected $connection = 'mongodb';
    protected $collection = 'site';
}
