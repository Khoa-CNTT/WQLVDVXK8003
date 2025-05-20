<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Kiểm tra từng cột trước khi thêm
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->unique();
            }

            if (!Schema::hasColumn('users', 'role_id')) {
                // Đảm bảo bảng roles đã tồn tại trước khi thêm khóa ngoại
                if (Schema::hasTable('roles')) {
                    $table->foreignId('role_id')->default(2)->constrained(); // 2 = customer role
                } else {
                    // Nếu bảng roles chưa tồn tại, chỉ thêm cột mà không có ràng buộc khóa ngoại
                    $table->unsignedBigInteger('role_id')->default(2);
                }
            }

            if (!Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'inactive', 'banned'])->default('active');
            }

            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Kiểm tra xem cột có tồn tại trước khi drop
            if (Schema::hasColumn('users', 'role_id')) {
                // Kiểm tra xem foreign key có tồn tại không
                $foreignKeys = Schema::getConnection()
                    ->getDoctrineSchemaManager()
                    ->listTableForeignKeys('users');

                $foreignKeyExists = false;
                foreach ($foreignKeys as $key) {
                    if (in_array('role_id', $key->getColumns())) {
                        $foreignKeyExists = true;
                        break;
                    }
                }

                if ($foreignKeyExists) {
                    $table->dropForeign(['role_id']);
                }
            }

            // Chỉ drop các cột nếu chúng tồn tại
            $columnsToDrop = [];
            foreach (['phone', 'role_id', 'status', 'last_login_at'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
