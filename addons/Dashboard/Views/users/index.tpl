<div class="content">
    <div class="block">
        <div class="block-header">
            <h3 class="block-title">{t code="All users"}</h3>
        </div>
        <div class="block-content">
            <table class="table table-hover vertical-center">
                <thead>
                    <tr>
                        <th class="fs-13 text-center hidden-xs hidden-sm">ID</th>
                        <th class="fs-13 text-center"><i class="fa fa-user-circle"></i></th>
                        <th class="fs-13">{t code="Login"}</th>
                        <th class="fs-13 hidden-xs hidden-sm">{t code="Name"}</th>
                        <th class="fs-13 hidden-xs hidden-sm hidden-md">Email</th>
                        <th class="fs-13 hidden-xs hidden-sm" style="width: 15%;">{t code="Groups"}</th>
                        <th class="fs-13 hidden-xs hidden-sm hidden-md" style="width: 15%;">{t code="Date inserted"}</th>
                        <th class="fs-13 hidden-xs hidden-sm text-center" style="width: 15%;">{t code="Active"}</th>
                        <th class="fs-13 text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$users item=user}
                    <tr>
                        <td class="font-w600 text-center hidden-xs hidden-sm">{$user.id}</td>
                        <td class="text-center">
                            <img class="img-avatar img-avatar32" src="https://www.gravatar.com/avatar/{$user.email|md5}" alt="">
                        </td>
                        <td class="font-w600">{$user.login}</td>
                        <td class="hidden-xs hidden-sm">{$user.name}</td>
                        <td class="hidden-xs hidden-sm hidden-md">{$user.email}</td>
                        <td class="hidden-xs hidden-sm">
                            {foreach from=explode(';', $user.groups) item=group}
                            <span class="label label-primary">{$group}</span>
                            {/foreach}
                        </td>
                        <td class="hidden-xs hidden-sm hidden-md">
                            <em class="text-muted">{$user.dateInserted|date_convert:datetime}</em>
                        </td>
                        <td class="hidden-xs hidden-sm text-center">
                            {if $user.active}{t code="Yes"}{else}{t code="No"}{/if}
                        </td>
                        <td class="text-right">
                            <div class="btn-group inlile-group" style="min-width: 112px;">
                                {if checkPermission('dashboard.user.edit')}
                                <a class="btn btn-default" title="{t code="Edit"}" href="/dashboard/users/edit/{$user.id}"><i class="fa fa-pencil"></i></a>
                                {/if}
                                {if checkPermission('dashboard.admin')}
                                <a class="btn btn-default" title="{t code="Log in as user"}" href="/dashboard/users/forceauth/{$user.id}" data-confirm="true"><i class="fa fa-sign-in"></i></a>
                                {/if}
                                {if checkPermission('dashboard.user.delete')}
                                <a class="btn btn-default" title="{t code="Remove"}" href="/dashboard/users/user/{$user.id}" data-confirm="true"><i class="fa fa-times"></i></a>
                                {/if}
                            </div>
                        </td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {if checkPermission('dashboard.user.add')}
        <a class="btn btn-primary" href="/dashboard/users/add"><i class="fa fa-plus with-text"></i> {t code="Add new user"}</a>
        {/if}
    </div>
</div>

                