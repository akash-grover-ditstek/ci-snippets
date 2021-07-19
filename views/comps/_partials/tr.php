<tr id="<?= $id ?>">
    <td>
        <?= isset($business_name) ? $business_name : '-' ?>
    </td>
    <td>
        <?= isset($sale_price) ? maskMoney($sale_price) : '-' ?>
    </td>
    <td>
        <?= isset($building_size) ? maskArea($building_size) : '-' ?>
    </td>
    <td>
        <?= isset($price_square_foot)? maskMoney($price_square_foot) : '-' ?>
    </td>
    <td>
        <?= isset($industry) ? get_property_type($industry) : '-' ?>
    </td>
    <td>
        <?= isset($date_sold) ? formatDate("m/d/Y", $date_sold) : '-' ?>
    </td>
    <td>
        <a onclick="CompsController.openForm(<?= $id ?>)" data-activates="slide-out"
           class="button-collapse"> <i class="material-icons">remove_red_eye</i></a>
    </td>
</tr>