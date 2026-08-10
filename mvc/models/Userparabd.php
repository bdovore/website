<?php

require_once __DIR__ . '/ParabdDbLine.php';
require_once __DIR__ . '/ParabdRules.php';

class Userparabd extends ParabdDbLine
{
    public $table_name = 'users_parabd';

    public function __construct($id = null)
    {
        parent::__construct($this->table_name, is_array($id) ? $id : array('ID_COPY' => $id));
    }

    public function copies($userId, $state = null, $publicOnly = false)
    {
        $where = 'c.USER_ID=' . intval($userId) . " AND i.STATUS='ACTIVE'";
        if ($state) $where .= " AND c.STATE='" . $this->escape($state) . "'";
        $mediaPath = Bdo_Cfg::getVar('explicit') ? 'm.FILE_PATH' : "IF(m.IS_EXPLICIT=1,CONCAT('?source=',m.FILE_PATH),m.FILE_PATH)";
        $fields = $publicOnly
            ? "c.ID_COPY,c.USER_ID,c.ITEM_ID,c.STATE,c.QUANTITY,c.COPY_NUMBER,c.COPY_IS_SIGNED,c.COPY_IS_DEDICATED,c.IS_PRICE_PUBLIC,IF(c.IS_PRICE_PUBLIC=1,c.PRICE,NULL) PRICE,IF(c.IS_PRICE_PUBLIC=1,c.CURRENCY,NULL) CURRENCY,c.CREATED_AT,c.UPDATED_AT"
            : 'c.*';
        return $this->fetchAllQuery("SELECT $fields,i.TITLE,i.TYPE_ID,t.LABEL TYPE_LABEL,st.LABEL SUBTYPE_LABEL,$mediaPath PRIMARY_IMAGE,m.IS_EXPLICIT PRIMARY_IMAGE_IS_EXPLICIT
            FROM users_parabd c JOIN parabd_item i ON i.ID_ITEM=c.ITEM_ID JOIN parabd_type t ON t.ID_TYPE=i.TYPE_ID
            LEFT JOIN parabd_type st ON st.ID_TYPE=i.SUBTYPE_ID LEFT JOIN parabd_media m ON m.ITEM_ID=i.ID_ITEM AND m.IS_PRIMARY=1 AND m.IS_HIDDEN=0
            WHERE $where ORDER BY c.CREATED_AT DESC");
    }

    public function saveForUser($userId, array $input)
    {
        $itemId = intval(isset($input['item_id']) ? $input['item_id'] : 0);
        if (!$this->fetchOneQuery("SELECT ID_ITEM FROM parabd_item WHERE ID_ITEM=$itemId AND STATUS='ACTIVE'")) throw new ParabdException('NOT_FOUND', 'Objet Para-BD introuvable.');
        $copyId = intval(isset($input['copy_id']) ? $input['copy_id'] : 0);
        $state = isset($input['state']) && $input['state'] === 'WISHLIST' ? 'WISHLIST' : 'OWNED';
        $quantity = $this->quantity(isset($input['quantity']) ? $input['quantity'] : 1);
        $copyNumber = trim(isset($input['copy_number']) ? $input['copy_number'] : '');
        if (mb_strlen($copyNumber, 'UTF-8') > 80) throw new ParabdException('VALIDATION_ERROR', 'Le numéro d’exemplaire est trop long.', array('copy_number' => '80 caractères maximum.'));
        $condition = strtoupper(trim(isset($input['condition_code']) ? $input['condition_code'] : 'UNKNOWN'));
        if (!in_array($condition, array('UNKNOWN','MINT','NEAR_MINT','VERY_GOOD','GOOD','FAIR','POOR'), true)) throw new ParabdException('VALIDATION_ERROR', 'État de conservation invalide.', array('condition_code' => 'Valeur invalide.'));
        $purchaseDate = $this->purchaseDate(isset($input['purchase_date']) ? $input['purchase_date'] : '');
        $price = ParabdRules::decimal(isset($input['price']) ? $input['price'] : null);
        $currency = strtoupper(trim(isset($input['currency']) ? $input['currency'] : 'EUR'));
        if ($price !== null && !preg_match('/^[A-Z]{3}$/', $currency)) throw new ParabdException('VALIDATION_ERROR', 'Devise ISO-3 invalide.');
        $seller = trim(isset($input['seller']) ? $input['seller'] : '');
        if (mb_strlen($seller, 'UTF-8') > 255) throw new ParabdException('VALIDATION_ERROR', 'Le nom du vendeur est trop long.', array('seller' => '255 caractères maximum.'));
        $data = array(
            'USER_ID' => intval($userId),
            'ITEM_ID' => $itemId,
            'STATE' => $state,
            'QUANTITY' => $quantity,
            'COPY_NUMBER' => $copyNumber,
            'CONDITION_CODE' => $condition,
            'COPY_IS_SIGNED' => ParabdRules::tri(isset($input['copy_is_signed']) ? $input['copy_is_signed'] : ''),
            'COPY_IS_DEDICATED' => ParabdRules::tri(isset($input['copy_is_dedicated']) ? $input['copy_is_dedicated'] : ''),
            'HAS_BOX' => ParabdRules::tri(isset($input['has_box']) ? $input['has_box'] : ''),
            'HAS_CERTIFICATE' => ParabdRules::tri(isset($input['copy_has_certificate']) ? $input['copy_has_certificate'] : ''),
            'IS_GIFT' => !empty($input['is_gift']) ? 1 : 0,
            'PURCHASE_DATE' => $purchaseDate,
            'PRICE' => $price,
            'CURRENCY' => $price === null ? null : $currency,
            'IS_PRICE_PUBLIC' => !empty($input['is_price_public']) ? 1 : 0,
            'SELLER' => $seller,
            'ESTIMATED_VALUE' => ParabdRules::decimal(isset($input['estimated_value']) ? $input['estimated_value'] : null),
            'PERSONAL_NOTES' => trim(isset($input['personal_notes']) ? $input['personal_notes'] : '')
        );
        if ($copyId) {
            if (!$this->fetchOneQuery("SELECT ID_COPY FROM users_parabd WHERE ID_COPY=$copyId AND USER_ID=" . intval($userId))) throw new ParabdException('NOT_FOUND', 'Exemplaire introuvable.');
            $data['ID_COPY'] = $copyId;
            (new self($copyId))->persist($data);
            return $copyId;
        }
        return intval((new self())->persist($data));
    }

    public function removeForUser($userId, $copyId)
    {
        $this->executeQuery('DELETE FROM users_parabd WHERE ID_COPY=' . intval($copyId) . ' AND USER_ID=' . intval($userId));
        if (Db_affected_rows($this->connection()) !== 1) throw new ParabdException('NOT_FOUND', 'Exemplaire introuvable.');
    }

    private function quantity($value)
    {
        $value = trim((string) $value);
        if (!ctype_digit($value) || intval($value) < 1 || intval($value) > 65535) throw new ParabdException('VALIDATION_ERROR', 'La quantité doit être comprise entre 1 et 65 535.', array('quantity' => 'Quantité invalide.'));
        return intval($value);
    }

    private function purchaseDate($value)
    {
        $value = trim((string) $value);
        if ($value === '') return null;
        if (preg_match('#^(\d{4})-(\d{2})-(\d{2})$#', $value, $match) && checkdate(intval($match[2]), intval($match[3]), intval($match[1]))) return $match[3] . '/' . $match[2] . '/' . $match[1];
        if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $value, $match) && checkdate(intval($match[2]), intval($match[1]), intval($match[3]))) return $value;
        throw new ParabdException('VALIDATION_ERROR', 'La date d’achat est invalide.', array('purchase_date' => 'Format attendu : JJ/MM/AAAA.'));
    }

    public function moveItem($sourceId, $targetId)
    {
        $this->executeQuery('UPDATE users_parabd SET ITEM_ID=' . intval($targetId) . ' WHERE ITEM_ID=' . intval($sourceId));
    }
}
