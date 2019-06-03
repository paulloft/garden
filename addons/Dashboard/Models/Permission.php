<?php

namespace Addons\Dashboard\Models;

use Garden\Cache;
use Garden\Gdn;
use Garden\Db\DB;
use Garden\Helpers\Arr;
use Garden\Model;
use Garden\Traits\Instance;

class Permission {
    public $capture = [];
    public $captureOnly = false;
    public $addonEnabled = true;

    protected $define = [];
    protected $disabled = [];

    private $_table = 'permissions';
    private $_groupTable = 'groups_permissions';

    use Instance;

    /**
     * Define permission
     *
     * @param $permission
     * @param bool $default
     * @return $this
     */
    public function define($permission, $default = false): self
    {
        $permission = strtolower($permission);
        if ($this->addonEnabled) {
            $this->define[$permission] = $default ? 1 : 0;
        } else {
            $this->disabled[$permission] = $default ? 1 : 0;
        }

        return $this;
    }

    /**
     * save permission
     */
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
            if (isset($this->disabled[$permission])) {
                continue;
            }
            $fields = [
                'code' => $permission,
                'def' => $this->define[$permission],
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
            if (isset($this->disabled[$permission])) {
                continue;
            }

            $id = $permissions[$permission];
            if ($this->captureOnly) {
                $this->capture[] = [
                    'code' => $permission,
                    'action' => 'delete'
                ];
            } else {
                $this->deleteID($id);
                $groupModel->deleteWhere(['id' => $id]);
            }
        }

        $this->define = [];
    }

    /**
     * Сhecks if the user has selected permissions
     *
     * @param string $permission
     * @param int $userID
     * @return bool
     */
    public function check(string $permission, $userID = null): bool
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

    /**
     * Get all user permissions
     *
     * @param int $userID
     * @return array
     */
    public function get($userID = null): array
    {
        if ($userID === null) {
            $userID = Session::currentUserID();
        }

        if (!$userID) {
            return [];
        }

        $cache = Cache::instance();
        $cacheKey = "permission_user_$userID";
        $return = $cache->get($cacheKey);

        if ($return !== null) {
            return (array)$return;
        }

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

        $cache->set($cacheKey, $return);

        return $return;
    }

    /**
     * Get list of all permisions
     * @param bool $formatted
     * @return array
     */
    public function getList(bool $formatted = false): array
    {
        $query = DB::select('*')
            ->from($this->_table)
            ->order_by('sort', 'asc');

        $permissions = $query->execute()->as_array();

        if (!$formatted) {
            return $permissions;
        }

        $result = [];
        foreach ($permissions as $permission) {
            list($group, $module, $action) = explode('.', $permission['code']);
            if (!$action) {
                $action = 'view';
            }

            if (!in_array($action, $result[$group]['columns'], true)) {
                $result[$group]['columns'][] = $action;
            }

            $result[$group]['items'][$module][$action] = [
                'id' => $permission['id'],
                'code' => $permission['code'],
                'default' => $permission['def']
            ];
        }

        return $result;
    }

    /**
     * Get all group permissions
     *
     * @param $groupID
     * @return array
     */
    public function getForGroup($groupID): array
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

    /**
     * save new permissions for group
     *
     * @param int $groupID
     * @param array $data
     * @param array $oldData
     */
    public function saveForGroup($groupID, $data, $oldData)
    {
        $newPerm = $data['permission'] ?? [];
        $oldPerm = $oldData['permission'] ?? [];

        $insert = array_diff($newPerm, $oldPerm);
        $delete = array_diff($oldPerm, $newPerm);

        $groupModel = Model::instance($this->_groupTable);
        foreach ($insert as $permissionID) {
            $groupModel->insert([
                'group_id' => $groupID,
                'permission_id' => $permissionID
            ]);
        }

        if (!empty($delete)) {
            $groupModel->deleteWhere(['group_id' => $groupID, 'permission_id' => $delete]);
        }
    }

    /**
     * Get permission ID
     *
     * @param $permission
     * @return mixed
     */
    public function getID(string $permission)
    {
        $query = DB::select('id')
            ->from($this->_table)
            ->where('code', '=', $permission)
            ->limit(1);

        return $query->execute()->get('id');
    }

    /**
     * Update permission
     *
     * @param int $id
     * @param array $data
     */
    protected function update($id, array $data)
    {
        DB::update($this->_table)
            ->set($data)
            ->where('id', '=', $id)
            ->execute();
    }

    /**
     * Insert new permission
     *
     * @param $data
     * @return mixed
     */
    protected function insert($data)
    {
        $columns = array_keys($data);

        $result = DB::insert($this->_table, $columns)
            ->values($data)
            ->execute();

        list($insertID) = $result;

        return $insertID;
    }

    /**
     * @param $id
     * @return int
     */
    protected function deleteID($id): int
    {
        return (int)DB::delete($this->_table)
            ->where('id', '=', $id)
            ->execute();
    }
}