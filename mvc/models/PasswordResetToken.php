<?php

/**
 *
 * @author laurent
 *
 */
class PasswordResetToken extends Bdo_Db_Line
{

    public $table_name = 'password_reset_tokens';

    /**
     */
    public function __construct ($id = null)
    {
        if (is_array($id)) {
            $a_data = $id;
        }
        else {
            $a_data = array(
                    'id' => $id
            );
        }
        parent::__construct($this->table_name, $a_data);
    }

    public function select() {
        return "SELECT `id`, `user_id`, `token`, `expires_at`, `created_at`
            FROM `$this->table_name` ";
    }

}
