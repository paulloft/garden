<?php

namespace Addons\Dashboard\Controllers;

use Addons\Dashboard\Models as Model;
use Addons\Dashboard\Modules\Sidebar;
use Garden\Exception;
use Garden\Form;
use Garden\Renderers\Template;
use Garden\Response;

class Users extends Model\Page
{

    /**
     * User list page
     *
     * @return Template
     * @throws Exception\Forbidden
     */
    public function index(): Template
    {
        $this->permission('dashboard.user.view');

        $userModel = Model\Users::instance();
        $users = $userModel->getWhere();

        return Model\Template::get()
            ->setTitle('Users')
            ->setData('users', $users->as_array());
    }

    /**
     * Add new user
     *
     * @return Template
     * @throws Exception\Forbidden
     * @throws Exception\NotFound
     */
    public function add(): Template
    {
        return $this->edit(false);
    }

    /**
     * Edit user
     *
     * @param $id
     * @return Template
     * @throws Exception\Forbidden
     * @throws Exception\NotFound
     */
    public function edit($id): Template
    {
        $userModel = Model\Users::instance();
        $groupModel = Model\Groups::instance();

        if ($id) {
            $this->permission('dashboard.user.edit');
            if (!$user = $userModel->getID($id)) {
                throw new Exception\NotFound();
            }

            $user['groupsID'] = $user['groupsID'] ? explode(';', $user['groupsID']) : [];
        } else {
            $this->permission('dashboard.user.add');
            $user = ['active' => 1, 'groupsID' => []];
        }

        $groups = $groupModel->getWhere(['active' => 1])->as_array();

        $form = new Form($userModel, $user);

        if ($form->submitted()) {
            $newPass = $form->getValue('newpassword');
            if (!empty($newPass)) {
                $form->setFormValue('password', $newPass);
            }

            $savedID = $form->save();
            if ($savedID) {
                $userModel->updateGroups($savedID, $form->getValues(), $user['groupsID']);
                Response::current()->redirect('/dashboard/users');
            }
        }

        Sidebar::instance()->setCurrentUrl('/dashboard/users');

        return Model\Template::get()
            ->setView('user_edit')
            ->setTitle($id ? 'Edit user' : 'New user')
            ->setDataArray([
                'user' => $user,
                'groups' => $groups,
                'form' => $form,
                'errors' => $form->errors(),
                'data' => $form->getValues()
            ]);
    }

    /**
     * Deleting user
     *
     * @param $id
     * @throws Exception\Forbidden
     */
    public function deleteUser($id)
    {
        $this->permission('dashboard.user.delete');

        if ((int)$id === 1) {
            throw new Exception\Forbidden('This operation is not allowed for this user');
        }

        $userModel = Model\Users::instance();
        $userModel->deleteID($id);

        Response::current()->redirect('/dashboard/users');
    }

    /**
     * User groups list
     *
     * @return Template
     * @throws Exception\Forbidden
     */
    public function groups(): Template
    {
        $this->permission('dashboard.group.view');

        $groupModel = Model\Groups::instance();
        $groups = $groupModel->getWhere();

        return Model\Template::get()
            ->setTitle('User groups')
            ->setData('groups', $groups->as_array());
    }

    /**
     * Add new user group
     *
     * @return Template
     * @throws Exception\Forbidden
     * @throws Exception\NotFound
     */
    public function groupAdd(): Template
    {
        return $this->groupEdit(false);
    }

    /**
     * Edit user group
     *
     * @param bool $id
     * @return Template
     * @throws Exception\Forbidden
     * @throws Exception\NotFound
     */
    public function groupEdit($id): Template
    {
        $groupModel = Model\Groups::instance();
        $permission = Model\Permission::instance();

        if ($id) {
            $this->permission('dashboard.group.edit');
            if (!$group = $groupModel->getID($id)) {
                throw new Exception\NotFound();
            }

            $groupPerm = $permission->getForGroup($id);
            $group['permission'] = array_column($groupPerm, 'id');
        } else {
            $this->permission('dashboard.group.add');
            $group = ['active' => 1];
        }

        $permList = $permission->getList(true);

        $form = new Form($groupModel, $group);

        if ($form->submitted()) {
            $savedID = $form->save();
            if ($savedID) {
                $permission->saveForGroup($savedID, $form->getValues(), $group);
                Response::current()->redirect('/dashboard/users/groups');
            }
        }

        Sidebar::instance()->setCurrentUrl('/dashboard/users/groups');

        return Model\Template::get()
            ->setTitle($id ? 'Edit user group' : 'New user group')
            ->setView('group_edit')
            ->addJs('user_group.js')
            ->setData('group', $group)
            ->setData('permList', $permList)
            ->setData('form', $form)
            ->setData('data', $form->getValues())
            ->setData('errors', $form->errors());
    }

    /**
     * Authorize as another user
     * @param $id
     * @throws Exception\Forbidden
     */
    public function forceAuth($id)
    {
        $this->permission('dashboard.user.view');

        $auth = Model\Auth::instance();
        $auth->forceLogin($id);

        Response::current()->redirect('/dashboard');
    }

    /**
     * Deleting group
     *
     * @param $id
     * @throws Exception\Forbidden
     */
    public function deleteGroup($id)
    {
        $this->permission('dashboard.group.delete');

        if ((int)$id === 1) {
            throw new Exception\Forbidden('This operation is not allowed for this group');
        }

        $groupModel = Model\Groups::instance();
        $groupModel->deleteID($id);

        Response::current()->redirect('/dashboard/users/groups');
    }

}