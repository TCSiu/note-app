<?php

namespace App\Commons;

use Illuminate\Database\Schema\Blueprint;

class GeneralSchema
{
    public const DEFAULT_COLLATION = 'utf8_unicode_ci';
	public const UTF8MB4_COLLATION = 'utf8mb4_unicode_ci';

    public static function generalFields(Blueprint $table = null){
        if($table == null) return false;
        $table->id();
		$table->uuid('uuid')->unasigned()->index();
		return $table;
    }

	public static function generalTimeStamp(Blueprint $table = null){
		if($table == null) return false;
		$table->timestamp('created_at')->nullable();
		$table->integer('created_by')->nullable();
		$table->timestamp('updated_at')->nullable();
		$table->integer('updated_by')->nullable();
		$table->boolean('is_active')->default(true);
		$table->boolean('is_deleted')->default(false);
		return $table;
	}
}