<tr>
    <td>
        <a href="<?= base_url('clients/' . ($client->canBeEdited() ? 'edit' : 'show') . '/' . $client->getId()) ?>"><?= $client->getName() ?></a>
    </td>

    <td>
        <?= $client->getCompany(); ?>
    </td>

    <td>
        <?= count($client->getEvaluations()); ?>
    </td>

    <td>
        <a href="<?= base_url('clients/' . ($client->canBeEdited() ? 'edit' : 'show') . '/' . $client->getId()) ?>">
            <img class="edit" src="<?= base_url('assets/img/visibility-button.png') ?>"/>
        </a>

        <?php if ($client->canBeEdited()) : ?>
            <a onclick="return confirm('Are you sure you want to continue?');"
               href="<?= base_url('clients/delete/' . $client->getId()) ?>">
                <img class="delete" src="<?= base_url('assets/img/delete.png') ?>"/>
            </a>
        <?php endif; ?>
    </td>
</tr>