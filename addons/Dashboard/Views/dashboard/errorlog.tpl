<div class="content">
    <div class="block">
        <div class="block-header bg-gray-lighter">
            <div class="block-title text-normal">
                <span class="font-w400">{t('Count error in log')}:</span> <strong>{count($data)}</strong>
            </div>
        </div>
        <div class="block-content">
            <!-- Messages Options -->
            <div class="push">
                <form method="get">
                    <div class="input-group">
                        <input type="date" class="form-control" name="date" value="{$date}"/>
                        <span class="input-group-btn">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-calendar"></i></button>
                        </span>
                    </div>
                </form>
            </div>
            <!-- END Messages Options -->

            <!-- Messages & Checkable Table (.js-table-checkable class is initialized in App() -> uiHelperTableToolsCheckable()) -->
            <div class="pull-r-l">
                <table class="js-table-checkable table table-hover table-vcenter">
                    <tbody>
                    {foreach $data as $error}
                        <tr>
                            <td class="text-center" style="width: 70px;">
                                <label class="css-input css-checkbox css-checkbox-primary">
                                    <input type="checkbox"><span></span>
                                </label>
                            </td>
                            <td class="hidden-xs font-w600" style="width: 140px;">{$error.file}<span class="text-muted" title="Line {$error.line}">: {$error.line}</span></td>
                            <td>
                                <div class="text-muted">{$error.text|format_form}</div>
                            </td>
                            <td class="visible-lg text-muted" style="width: 160px;">
                                <nobr><em>{date_convert($error.date, 'datetime')}</em></nobr>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
            <!-- END Messages -->
        </div>
    </div>
</div>

