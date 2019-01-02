<div class="content">
    <div class="block">
        <div class="block-content">
            {form id="addons_form"}
            {input type="hidden" name="addon"}
            {input type="hidden" name="enable"}
            {form_errors}
                <table class="table" id="addons-choise">
                    <thead>
                    <tr>
                        <th width="1%"></th>
                        <th width="40%">{"Addon name"|translate}</th>
                        <th width="58%">{"Description"|translate}</th>
                        <th width="1%">{"Code"|translate}</th>
                        <th width="1%">{"Version"|translate}</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $addons as $name => $addon}
                        {if valr('info.system', $addon)}{continue}{/if}
                        <tr>
                            <td>
                                <label class="css-input switch switch-sm switch-success mt-0 mb-0">
                                    {checkbox name=$name}
                                    <span></span>
                                </label>
                            </td>
                            <td>
                                {valr('info.name', $addon, $name)}
                            </td>
                            <td>
                                {valr('info.description', $addon)}
                            </td>
                            <td>
                                {$name}
                            </td>
                            <td>
                                {valr('info.version', $addon)}
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            {/form}
        </div>
    </div>
</div>

{literal}
    <script>
        var form = $('#addons_form');
        $('#addons-choise input[type="checkbox"]').change(function () {
            form.find('input[name="addon"]').val( $(this).attr('name') );
            form.find('input[name="enable"]').val( $(this).is(':checked') ? 1 : 0 );
            form.submit();
        });
    </script>
{/literal}
