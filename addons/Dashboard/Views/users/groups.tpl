<div class="content">
    <div class="block">
        <div class="block-header">
            <h3 class="block-title">{"All groups"|translate}</h3>
        </div>
        <div class="block-content">
            <table class="table table-hover vertical-center">
                <thead>
                    <tr>
                        <th class="fs-13 text-center hidden-xs hidden-sm">ID</th>
                        <th width="20%" class="fs-13">{"Group name"|translate}</th>
                        <th class="fs-13 hidden-xs hidden-sm hidden-md">{"Description"|translate}</th>
                        <th class="fs-13 hidden-xs hidden-sm hidden-md" style="width: 15%;">{"Date inserted"|translate}</th>
                        <th class="fs-13 hidden-xs hidden-sm text-center" style="width: 15%;">{"Active"|translate}</th>
                        <th class="fs-13 text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$groups item=group}
                    <tr>
                        <td class="font-w600 text-center hidden-xs hidden-sm">{$group.id}</td>
                        <td class="font-w600">{$group.name}</td>
                        <td class="hidden-xs hidden-sm hidden-md">{$group.description}</td>
                        <td class="hidden-xs hidden-sm hidden-md">
                            <em class="text-muted">{Date::create($group.created_at)->toDateTime()}</em>
                        </td>
                        <td class="hidden-xs hidden-sm text-center">
                            {if $group.active}{"Yes"|translate}{else}{"No"|translate}{/if}
                        </td>
                        <td class="text-right">
                            <div class="btn-group inlile-group" style="min-width: 74px;">
                            {if checkPermission('dashboard.group.edit')}
                                <a class="btn btn-default" title="{"Edit"|translate}" href="/dashboard/users/groupedit/{$group.id}"><i class="fa fa-pencil"></i></a>
                            {/if}
                            {if $group.id != 1 AND checkPermission('dashboard.group.delete')}
                                <a class="btn btn-default" title="{"Remove"|translate}" href="/dashboard/users/deletegroup/{$group.id}" data-action="confirm"><i class="fa fa-times"></i></a>
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
        {if checkPermission('dashboard.group.add')}
        <a class="btn btn-primary" href="/dashboard/users/groupadd"><i class="fa fa-plus with-text"></i> {"New group"|translate}</a>
        {/if}
    </div>
</div>

                