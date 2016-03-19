<div class="container">
    <h1>{$title}</h1>
    {if $capturedSql}
    <pre>
{foreach from=$capturedSql item=sql}{$sql}

{/foreach}
    </pre>
    {else}
    <div class="alert alert-info" role="alert">{t code="Nothing to update"}</div>
    {/if}
    <a href="?refresh" class="btn btn-primary">Пересканировать</a>
    <a href="?update" class="btn btn-primary">Запустить обновление структуры</a>
</div>

