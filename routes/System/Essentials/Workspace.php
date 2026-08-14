<?php

use App\Http\Controllers\System\Essentials\{WorkspaceController};
use Illuminate\Support\Facades\{Route};

Route::get("", [WorkspaceController::class, "index"])->name("workspace.index");
