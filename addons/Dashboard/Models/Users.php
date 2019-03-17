<?php
namespace Addons\Dashboard\Models;
use Garden\Form;
use Garden\Gdn;
use Garden\Db\DB;
use Garden\Model;

/**
 * Base users model
 */

class Users extends \Garden\Model
{
    public function __construct()
    {
        parent::__construct('users');
    }

    public function getID($id)
    {
        $result = Gdn::cache('dirty')->get('user_' . $id);

        if (!$result) {
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

            Gdn::cache('dirty')->set('user_' . $id, $result);
        }

        return $result;
    }

    public function getUserName($id)
    {
        $user = $this->getID($id);
        return val('name', $user);
    }

    public function getUserLogin($id)
    {
        $user = $this->getID($id);
        return val('login', $user);
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

    public function delete(array $where = [])
    {
        $this->_query = DB::update($this->table)
            ->set([
                'deleted'     => 1,
                'dateDeleted' => DB::expr('now()'),
                'userDeleted' => $this->userID,
            ]);

        $this->_where($where);
        $this->_query->execute();

        return true;
    }

    public function deleteID($id)
    {
        Groups::instance()->delete(['user_id' => $id]);

        return parent::deleteID($id);
    }

    public function updateGroups($userID, $post, $groups)
    {
        $groupModel = Groups::instance();
        $groupsNew = val('groupsID', $post, []);

        $insert = array_diff($groupsNew, $groups);
        $delete = array_diff($groups, $groupsNew);

        foreach ($insert as $groupID) {
            $groupModel->insert([
                'user_id'  => $userID,
                'group_id' => $groupID
            ]);
        }

        if (!empty($delete)) {
            $groupModel->delete(['user_id' => $userID, 'group_id' => $delete]);
        }

    }

    public function loginAvailable($login, $id = false)
    {
        $where = ['login' => $login];
        if ($id) {
            $where['id<>'] = $id;
        }

        return  $this->getCount($where) <= 0;
    }

    public function emailAvailable($email, $id = false)
    {
        $where = ['email' => $email];
        if ($id) {
            $where['id<>'] = $id;
        }

        return  $this->getCount($where) <= 0;
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
        $password = val('password', $post);
        if ($password) {
            $password = Auth::instance()->hash($password);
            $post['password'] = $password;
        }

        return parent::save($post, $id);
    }

}