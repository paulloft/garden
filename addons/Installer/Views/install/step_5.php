<?php echo $form->open(['action' => '/install?step=5']); ?>
    <div class="block-content">
        <h3>Addons</h3>
        <p>Select the addons to be installed</p>
        <table class="table">
            <thead>
                <tr>
                    <th width="1%"></th>
                    <th width="40%">Name</th>
                    <th width="58%">Description</th>
                    <th width="1%">Version</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($addons as $name => $addon): ?>
                <?php if ($name === 'installer') {continue;} ?>
                <tr>
                    <td>
                        <label class="css-input switch switch-sm switch-success mt-0 mb-0">
                            <?php echo $form->checkbox($name); ?>
                            <span></span>
                        </label>
                    </td>
                    <td>
                        <?php echo $addon['info']['name'] ?? $name; ?>
                    </td>
                    <td>
                        <?php echo $addon['info']['description']; ?>
                    </td>
                    <td>
                        <?php echo $addon['info']['version']; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>


    </div>
    <hr>
    <div class="block-header">
        <div class="row">
            <div class="col-xs-6">
                <a href="/install?step=4" class="btn btn-default">
                    <i class="fa fa-chevron-left"></i> Step back
                </a>
            </div>
            <div class="col-xs-6 text-right">
                <button type="submit" class="btn btn-success">
                    Continue <i class="fa fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
<?php echo $form->close(); ?>