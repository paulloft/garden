<?php

namespace Addons\Dashboard\Models;

use Garden\Cache;
use Garden\Form;
use Garden\Db\DB;
use Garden\Model;

/**
 * Base users model
 */
class Users extends Model {

    public function __construct()
    {
        parent::__construct('users');
    }

    /**
     * Get user by ID
     *
     * @param int $id
     * @return array
     */
    public function getID($id)
    {
        $result = Cache::lazyGet("user_$id");

        if ($result !== null) {
            return $result;
        }

        $query = DB::select('u.*')
            ->select(DB::expr("GROUP_CONCAT(DISTINCT g.id ORDER BY g.sort ASC SEPARATOR ';') AS groupsID"))
            ->select(DB::expr("GROUP_CONCAT(DISTINCT g.name ORDER BY g.sort ASC SEPARATOR ';') AS groups"))
            ->from($this->table, 'u')
            ->join('users_groups', 'ug', 'LEFT')
            ->on('u.id', '=', 'ug.user_id')
            ->join('groups', 'g', 'LEFT')
            ->on('ug.group_id', '=', 'g.id')
            ->where('u.id', '=', $id)
            ->limit(1);

        $result = $query->execute()->current();

        Cache::lazySet("user_$id", $result);

        return $result;
    }

    public function getUserName($id)
    {
        $user = $this->getID($id);
        return $user['name'] ?? '';
    }

    public function getUserLogin($id)
    {
        $user = $this->getID($id);
        return $user['login'] ?? '';
    }

    public function getByLogin($username)
    {
        return $this->getWhere(['login' => $username])->current();
    }

    public function getByEmail($email)
    {
        return $this->getWhere(['email' => $email])->current();
    }

    protected function baseQuery(array $where = [], array $order = [], $limit = false, $offset = 0)
    {
        $this->_query = DB::select('u.*')
            ->select(DB::expr("GROUP_CONCAT(DISTINCT g.id ORDER BY g.sort ASC SEPARATOR ';') AS groupsID"))
            ->select(DB::expr("GROUP_CONCAT(DISTINCT g.name ORDER BY g.sort ASC SEPARATOR ';') AS groups"))
            ->from($this->table, 'u')
            ->join('users_groups', 'ug', 'LEFT')
            ->on('u.id', '=', 'ug.user_id')
            ->join('groups', 'g', 'LEFT')
            ->on('ug.group_id', '=', 'g.id')
            ->group_by('u.id');

        $this->_where($where);

        foreach ($order as $field => $direction) {
            $this->_query->order_by($field, $direction);
        }

        if ($limit) {
            $this->_query->limit($limit);
            $this->_query->offset($offset);
        }
    }

    public function getWhere(array $where = [], array $order = [], $limit = 0, $offset = 0)
    {
        $_where = $where;
        $where = [];
        foreach ($_where as $key => $value) {
            $where['u.' . $key] = $value;
        }

        $this->baseQuery($where, $order, $limit, $offset);

        return $this->_query->execute();
    }

    public function getByGroupID($groupID, array $where = [], array $order = [], $limit = false, $offset = 0)
    {
        $_where = $where;
        $where = [];
        foreach ($_where as $key => $value) {
            $where['u.' . $key] = $value;
        }

        $where['g.id'] = $groupID;

        $this->baseQuery($where, $order, $limit, $offset);

        return $this->_query->execute();
    }

    public function updateVisit($userID)
    {
        DB::update($this->table)
            ->set(['last_visit' => DB::expr('now()')])
            ->where($this->primaryKey, '=', $userID)
            ->execute();
    }

    public function deleteWhere(array $where = []): int
    {
        $usersID = $this->getWhere($where)->as_array(null, 'id');

        if (!empty($usersID)) {
            Groups::instance()->deleteWhere(['user_id' => $usersID]);
        }

        return parent::deleteWhere($where);
    }

    public function deleteID($id): int
    {
        Groups::instance()->deleteWhere(['user_id' => $id]);

        return parent::deleteID($id);
    }

    public function updateGroups($userID, $post, $groups)
    {
        $groupModel = Groups::instance();
        $groupsNew = $post['groupsID'] ?? [];

        $insert = array_diff($groupsNew, $groups);
        $delete = array_diff($groups, $groupsNew);

        foreach ($insert as $groupID) {
            $groupModel->insert([
                'user_id' => $userID,
                'group_id' => $groupID
            ]);
        }

        if (!empty($delete)) {
            $groupModel->deleteWhere(['user_id' => $userID, 'group_id' => $delete]);
        }

    }

    public function loginAvailable($login, $id = false)
    {
        $where = ['login' => $login];
        if ($id) {
            $where['id<>'] = $id;
        }

        return $this->getCount($where) <= 0;
    }

    public function emailAvailable($email, $id = false)
    {
        $where = ['email' => $email];
        if ($id) {
            $where['id<>'] = $id;
        }

        return $this->getCount($where) <= 0;
    }

    public function initFormValidation(Form $form)
    {
        $this->_validation
            ->rule('password', 'minLength', 6)
            ->rule('login', [$this, 'loginAvailable'], ':id')
            ->rule('email', 'email')
            ->rule('email', [$this, 'emailAvailable'], ':id');
    }

    public function save(array $post, $id = false)
    {
        if (isset($post['password'])) {
            $post['password'] = Auth::instance()->hash($post['password']);
        }

        return parent::save($post, $id);
    }

}