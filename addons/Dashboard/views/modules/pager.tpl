<nav>
    <ul class="pagination">
    {if $showArrows}
        {if $current <= $start}
        <li class="disabled"><span title="Предыдущая страница"><i class="fa fa-angle-double-left"></i></span></li>
        {else}
        <li><a href="{"<page>"|str_replace:($current-1):$uri}" title="Предыдущая страница"><i class="fa fa-angle-double-left"></i></a></li>
        {/if}
    {/if}

        {foreach from=$pages item=page}
        <li {if $page == $current}class="active" title="Текущая страница"{/if}>
            {if $page}<a href="{"<page>"|str_replace:$page:$uri}">{$page}</a>{else}<span>...</span>{/if}
        </li>
        {/foreach}

    {if $showArrows}
        {if $current >= $end}
        <li class="disabled"><span title="Следующая страница"><i class="fa fa-angle-double-right"></i></span></li>
        {else}
        <li><a href="{"<page>"|str_replace:($current+1):$uri}" title="Следующая страница"><i class="fa fa-angle-double-right"></i></a></li>
        {/if}
    {/if}
    </ul>
</nav>