<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Branch Module Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for the Branch module messages
    | and responses. These can be customized to match your application"s
    | requirements.
    |
    */

    // Success Messages
    "created" => "Branch created successfully.",
    "updated" => "Branch updated successfully.",
    "deleted" => "Branch deleted successfully.",
    "retrieved" => "Branch retrieved successfully.",

    // Error Messages
    "not_found" => "Branch not found.",
    "not_implemented" => "Functionality not implemented.",
    "create_failed" => "Failed to create branch.",
    "update_failed" => "Failed to update branch.",
    "delete_failed" => "Failed to delete branch.",
    "retrieve_failed" => "Failed to retrieve branch.",
    "delete_not_implemented" => "Delete functionality not implemented.",

    // Validation Messages
    "internal_code_exists" => "The internal code has already been registered.",
    "company_id_required" => "Company ID is required.",

    // General Messages
    "init_params_error" => "Error retrieving initialization parameters.",
    "list_error" => "Error retrieving branch list.",

    // Exception Messages
    "exception_create" => "Error creating branch: :message",
    "exception_update" => "Error updating branch: :message",
    "exception_delete" => "Error deleting branch: :message",
    "exception_retrieve" => "Error retrieving branch: :message",
];

