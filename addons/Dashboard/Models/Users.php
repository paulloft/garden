<?php
namespace Addons\Dashboard\Models;
use Garden\Gdn;
use Garden\DB;

/**
 * Base users model
 */

class Users extends \Garden\Model
{
    public $table = 'users';
    
    function __construct()
    {
        parent::__construct($this->table);
    }

    public function getID($id)
    {
        $result = Gdn::cache('dirty')->get('user_'.$id);

        if (!$result) {
            $query = DB::select('u.*')
                ->select(DB::Expr("GROUP_CONCAT(DISTINCT g.id ORDER BY g.sort ASC SEPARATOR ';') AS groupsID"))
                ->select(DB::Expr("GROUP_CONCAT(DISTINCT g.name ORDER BY g.sort ASC SEPARATOR ';') AS groups"))
                ->from($this->table, 'u')

                ->join('users_groups', 'ug', 'LEFT')
                  ->on('u.id', '=', 'ug.userID')

                ->join('groups', 'g', 'LEFT')
                  ->on('ug.groupID', '=', 'g.id')

                ->where('u.id', '=', $id)
                ->where('u.deleted', '=', 0)
                ->limit(1);

            $result = $query->execute()->current();

            Gdn::cache('dirty')->set('user_'.$id, $result);
        }

        return $result;
    }

    public function getLogin($username)
    {
        $result = $this->getWhere(array('login'=>$username))->current();

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

    public function getEmail($email)
    {
        $result = $this->getWhere(array('email'=>$email))->current();

        return $result;
    }

    protected function baseQuery($where = array(), $order = array(), $limit = false, $offset = 0)
    {
        $this->_query = DB::select('u.*')
            ->select(DB::Expr("GROUP_CONCAT(DISTINCT g.id ORDER BY g.sort ASC SEPARATOR ';') AS groupsID"))
            ->select(DB::Expr("GROUP_CONCAT(DISTINCT g.name ORDER BY g.sort ASC SEPARATOR ';') AS groups"))
            ->from($this->table, 'u')

            ->join('users_groups', 'ug', 'LEFT')
              ->on('u.id', '=', 'ug.userID')
              
            ->join('groups', 'g', 'LEFT')
              ->on('ug.groupID', '=', 'g.id')

            ->where('u.deleted', '=', 0)
            ->group_by('u.id');

        $this->_where($where);

        foreach ($order as $field => $direction) 
        {
            $this->_query->order_by($field, $direction);
        }

        if ($limit)
        {
            $this->_query->limit($limit);
            $this->_query->offset($offset);
        }
    }
     
    public function getWhere($where = array(), $order = array(), $limit = false, $offset = 0)
    {
        $_where = $where; $where = []; 
        foreach ($_where as $key => $value) {
            $where['u.'.$key] = $value;
        }

        $this->baseQuery($where, $order, $limit, $offset);

        return $this->_query->execute();
    }

    public function getByGroupID($groupID, $where = array(), $order = array(), $limit = false, $offset = 0)
    {
        $_where = $where; $where = []; 
        foreach ($_where as $key => $value) {
            $where['u.'.$key] = $value;
        }

        $where['g.id'] = $groupID;

        $this->baseQuery($where, $order, $limit, $offset);

        return $this->_query->execute();
    }

    public function updateVisit($userID)
    {
        DB::update($this->table)
            ->set(array('lastVisit' => DB::expr('now()')))
            ->where($this->primaryKey, '=', $userID)
            ->execute();
    }

    public function delete($where = array())
    {
        $this->_query = DB::update($this->table)
            ->set([
                'deleted' => 1,
                'dateDeleted' => DB::expr('now()')
            ]);

        $this->_where($where);  
        $this->_query->execute();
    }

    public function updateGroups($userID, $post, $groups)
    {
        $groupModel = Gdn::factory('\Garden\Model', 'users_groups');
        $groupsNew = val('groupsID', $post, array());

        $insert = array_diff($groupsNew, $groups);
        $delete = array_diff($groups, $groupsNew);

        foreach ($insert as $groupID)
        {
            $groupModel->insert(array(
                'userID' => $userID,
                'groupID' => $groupID
            ));
        }

        if (!empty($delete))
        {
            $groupModel->delete(array('userID' => $userID, 'groupID' => $delete));
        }

    }

    public function usernameAvailable($username, $id = false)
    {
        $where = array('login'=>$username);
        if ($id) $where['id<>'] = $id;

        $result = $this->getCount($where);

        return $result > 0 ? false : true;
    }

    public function emailAvailable($email, $id = false)
    {
        $where = array('email'=>$email);
        if ($id) $where['id<>'] = $id;

        $result = $this->getCount($where);

        return $result > 0 ? false : true;
    }
    
}