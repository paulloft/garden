<!-- Header Navigation Right -->
<ul class="nav-header pull-right">
    <li>
        <div class="btn-group">
            <button class="btn btn-default btn-image dropdown-toggle hidden-sm hidden-md hidden-lg" data-toggle="dropdown" type="button" aria-expanded="false">
                <img src="https://www.gravatar.com/avatar/{$user.email|md5}" alt="Avatar">
                <span class="caret"></span>
            </button>
            <button class="btn btn-link hidden-xs" data-toggle="dropdown" type="button">
                <img src="https://www.gravatar.com/avatar/{$user.email|md5}" alt="Avatar">
                <span class="name">{if $user.name}{$user.name}{else}{$user.login}{/if}</span>
                <span class="fa fa-chevron-down"></span>
            </button>


            <ul class="dropdown-menu dropdown-menu-right">
                <li class="dropdown-header">{"Profile"|translate}</li>
                <li>
                    <a tabindex="-1" href="/dashboard/users/edit/{$user.id}">
                        <i class="fa fa-cog pull-right"></i>{"Settings"|translate}
                    </a>
                </li>
                <li class="divider"></li>
                <li class="dropdown-header">{"Actions"|translate}</li>
                <li>
                    <a tabindex="-1" href="/entry/logout">
                        <i class="fa fa-sign-out pull-right"></i>{"Sign out"|translate}
                    </a>
                </li>
            </ul>
        </div>
    </li>
</ul>
<!-- END Header Navigation Right -->

<!-- Header Navigation Left -->
<ul class="nav-header pull-left">
    <li class="hidden-sm hidden-md hidden-lg">
        <button class="btn btn-link" data-action="menu_toggle"><i class="fa fa-bars" title="{"Toggle menu"|translate}"></i></button>
    </li>
{foreach from=$buttons item=button}
    <li><{$button.type} {$button.attributes}>{if $button.icon}<i class="{$button.icon}"></i> <span class="hidden-xs">{$button.name}</span>{else}{$button.name}{/if}</{$button.type}></li>
{/foreach}
</ul>
<!-- END Header Navigation Left -->