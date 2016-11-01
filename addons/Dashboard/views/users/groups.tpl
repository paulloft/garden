<div class="content">
    <div class="block">
        <div class="block-header">
            <h3 class="block-title">{t code="All groups"}</h3>
        </div>
        <div class="block-content">
            <table class="table table-hover vertical-center">
                <thead>
                    <tr>
                        <th class="fs-13 text-center hidden-xs hidden-sm">ID</th>
                        <th width="20%" class="fs-13">{t code="Group name"}</th>
                        <th class="fs-13 hidden-xs hidden-sm hidden-md">{t code="Description"}</th>
                        <th class="fs-13 hidden-xs hidden-sm hidden-md" style="width: 15%;">{t code="Date inserted"}</th>
                        <th class="fs-13 hidden-xs hidden-sm text-center" style="width: 15%;">{t code="Active"}</th>
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
                            <em class="text-muted">{$group.dateInserted|date_convert:datetime}</em>
                        </td>
                        <td class="hidden-xs hidden-sm text-center">
                            {if $group.active}{t code="Yes"}{else}{t code="No"}{/if}
                        </td>
                        <td class="text-right">
                            <div class="btn-group inlile-group" style="min-width: 74px;">
                            {if checkPermission('dashboard.group.edit')}
                                <a class="btn btn-default" title="{t code="Edit"}" href="/users/groupedit/{$group.id}"><i class="fa fa-pencil"></i></a>
                            {/if}
                            {if $group.id != 1 AND checkPermission('dashboard.group.delete')}
                                <button class="btn btn-default" title="{t code="Remove"}" data-href="/users/group/{$group.id}" data-confirm="true" data-layout="ajax" data-reload="true" data-ajax-type="delete"><i class="fa fa-times"></i></button>
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
        <a class="btn btn-primary" href="/users/groupadd"><i class="fa fa-plus with-text"></i> {t code="New group"}</a>
        {/if}
    </div>
</div>

                