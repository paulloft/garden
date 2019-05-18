<?php
namespace Addons\Dashboard\Models;
use Garden\Gdn;
use Garden\Db\DB;
use Garden\Helpers\Arr;
use Garden\Model;
use Garden\Traits\Instance;


class Permission
{
    public $capture = [];
    public $captureOnly = false;
    public $addonEnabled = true;

    protected $define = [];
    protected $disabled = [];

    private $_table = 'permissions';
    private $_groupTable = 'groups_permissions';

    use Instance;

    public function define($permission, $default = false)
    {
        $permission = strtolower($permission);
        if ($this->addonEnabled) {
            $this->define[$permission] = $default ? 1 : 0;
        } else {
            $this->disabled[$permission] = $default ? 1 : 0;
        }

        return $this;
    }

    public function save()
    {
        $permissions = $this->getList();
        $permissions = array_column($permissions, 'id', 'code');

        $oldPerm = array_keys($permissions);
        $newPerm = array_keys($this->define);

        $insert = array_diff($newPerm, $oldPerm);
        $delete = array_diff($oldPerm, $newPerm);

        $sort = count($permissions) - count($delete) + 1;

        foreach ($insert as $permission) {
            if (val($permission, $this->disabled) !== false) {
                continue;
            }
            $fields = [
                'code' => $permission,
                'def'  => val($permission, $this->define),
                'sort' => $sort++
            ];

            if ($this->captureOnly) {
                $fields['action'] = 'insert';
                $this->capture[] = $fields;
            } else {
                $this->insert($fields);
            }
        }

        $groupModel = Model::instance($this->_groupTable);
        foreach ($delete as $permission) {
            if (val($permission, $this->disabled) !== false) {
                continue;
            }

            $id = val($permission, $permissions);
            if ($this->captureOnly) {
                $this->capture[] = [
                    'code'   => $permission,
                    'action' => 'delete'
                ];
            } else {
                $this->delete($id);
                $groupModel->deleteWhere(['id'=>$id]);
            }
        }

        $this->define = [];
    }

    public function check($permission, $userID = false)
    {
        if (Auth::instance()->admin()) {
            return true;
        }

        $permissions = $this->get($userID);
        if (!$permissions) {
            return false;
        }

        $arrPerm = (array)$permission;

        foreach ($arrPerm as $perm) {
            if (!Arr::path($permissions, $perm)) {
                return false;
            }
        }

        return true;
    }

    public function get($userID = null)
    {
        if ($userID === null) {
            $userID = Session::currentUserID();
        }

        if (!$userID) {
            return false;
        }

        $cacheKey = "permission_user_$userID";

        if (!$return = Gdn::cache()->get($cacheKey)) {
            $result = DB::selectArray(['p.id', 'p.code'])
                ->from('users_groups', 'ug')

                ->join('groups', 'g')
                ->on('g.id', '=', 'ug.group_id')

                ->join($this->_groupTable, 'gp')
                ->on('gp.group_id', '=', 'ug.group_id')

                ->join($this->_table, 'p')
                ->on('p.id', '=', 'gp.permission_id')

                ->where('ug.user_id', '=', $userID)
                ->where('g.active', '=', 1)
                ->where('g.deleted', '=', 0)

                ->group_by('p.id')
                ->as_object()
                ->execute()
                ->as_array();

            $return = [];
            foreach ($result as $permission) {
                list($group, $module, $action) = explode('.', $permission->code);
                $return[$group][$module][$action] = $permission->id;
            }

            Gdn::cache()->set($cacheKey, $return);
        }

        return $return;
    }

    public function getList($formatted = false)
    {
        $query = DB::select('*')
            ->from($this->_table)
            ->order_by('sort', 'asc');

        if (!$formatted) {
            return $query->execute()->as_array();
        }

        $permissions = $query->as_object()->execute()->as_array();

        $result = [];
        foreach ($permissions as $permission) {
            list($group, $module, $action) = explode('.', $permission->code);
            if (!$action) {
                $action = 'view';
            }

            if (!in_array($action, $result[$group]['columns'])) {
                $result[$group]['columns'][] = $action;
            }

            $result[$group]['items'][$module][$action] = [
                'id' => $permission->id,
                'code' => $permission->code,
                'default' => $permission->def
            ];
        }

        return $result;
    }

    public function getForGroup($groupID)
    {
        $result = DB::select('gp.permission_id', 'id')
            ->select('p.code', 'code')
            ->from($this->_groupTable, 'gp')

            ->join($this->_table, 'p')
              ->on('gp.permission_id', '=', 'p.id')

            ->where('gp.group_id', '=', $groupID)
            ->execute()
            ->as_array();

        return $result;
    }

    public function saveGroup($groupID, $data, $oldData)
    {
        $oldPerm = val('permission', $oldData, []);
        $newPerm = val('permission', $data, []);

        $insert = array_diff($newPerm, $oldPerm);
        $delete = array_diff($oldPerm, $newPerm);

        $groupModel = Model::instance($this->_groupTable);
        foreach ($insert as $permissionID) {
            $groupModel->insert([
                'group_id'      => $groupID,
                'permission_id' => $permissionID
            ]);
        }

        if (!empty($delete)) {
            $groupModel->deleteWhere(['group_id' => $groupID, 'permission_id' => $delete]);
        }
    }

    public function getID($permission)
    {
        $query = DB::select('id')
            ->from($this->_table)
            ->where('code', '=', $permission)
            ->limit(1);

        $result = $query->execute()->current();
        return val('id', $result);
    }

    protected function update($id, $data)
    {
        DB::update($this->_table)
            ->set($data)
            ->where('id', '=', $id)
            ->execute();
    }

    protected function insert($data)
    {
        $columns = array_keys($data);

        $query = DB::insert($this->_table, $columns)
            ->values($data)
            ->execute();

        return val(0, $query, false);
    }

    protected function delete($id)
    {
        DB::delete($this->_table)
            ->where('id', '=', $id)
            ->execute();
    }
}