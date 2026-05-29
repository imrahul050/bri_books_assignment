<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait FileUpload
{
  public function handleSingleFileUpload($file, $path): string
  {
    $fileName = Str::random(30) . time() . '.' . $file->extension();
    $file->move($path, $fileName);
    return $fileName;
  }

}