<?php require_once(ENUM_DIR . 'EvaluationImageKey.php');?>

<div id="eval_menu">
    <ul>
        <?php $var = $this->uri->segment(2, 0); ?>
        <?php $id = $this->uri->segment(3, 0); ?>
        <?php $size = 'size-2'?>
        <?php $count = 0; ?>

        <?php if($evaluation->hasIncomeApproach()): ?>
            <?php $count += 1; ?>
        <?php endif; ?>

        <?php if($evaluation->hasSalesApproach()): ?>
            <?php $count += 1; ?>
        <?php endif; ?>

        <?php if($evaluation->hasCostApproach()): ?>
            <?php $count += 1; ?>
        <?php endif; ?>

        <?php if($count === 3): ?>
            <?php $size = 'size-2'; ?>
        <?php elseif($count === 2): ?>
            <?php $size = 'size-2-and-a-half'; ?>
        <?php elseif($count === 1): ?>
            <?php $size = 'size-3'; ?>
        <?php elseif($count === 0): ?>
            <?php $size = 'size-4'; ?>
        <?php endif; ?>

        <li class="main_drp_section <?= ($var === 'overview') || ($var === 'property-boundaries') || ($var === 'images')? 'active' : '' ?> <?= $size; ?>"><a href="<?= evaluation_url('overview', $id); ?>">Overview  <span class="fa fa-caret-down" aria-hidden="true"></span>
            </a><span class="rtriangle"></span>
            <div class="inner_drp">
                <a href="<?= evaluation_url('overview', $id); ?>" class="<?= ($var === 'overview') ? 'active' : '' ?>">Overview Page</a>
                <a href="<?= evaluation_url('images', $id); ?>" class="<?= ($var === 'images') ? 'active' : '' ?>">Images Page</a>
                <a href="<?= evaluation_url('property-boundaries', $id); ?>" class="<?= ($var === 'property-boundaries') ? 'active' : '' ?>"">Map Boundary Page</a>
            </div>
        </li>

        <?php if($evaluation->hasIncomeApproach()): ?>
            <li class="<?= ($var === 'income')? 'active' : '' ?> <?= $size; ?>"><span class="ltriangle"></span><a href="<?= evaluation_url('income', $id); ?>">Income Approach</a><span class="rtriangle"></span></li>
        <?php endif; ?>

        <?php if($evaluation->hasSalesApproach()): ?>
            <li class="<?= ($var === 'sales')? 'active' : '' ?> <?= $size; ?>"><span class="ltriangle"></span><a href="<?= evaluation_url('sales', $id); ?>">Sales Approach</a><span class="rtriangle"></span></li>
        <?php endif; ?>

        <?php if($evaluation->hasCostApproach()): ?>
            <li class="main_drp_section <?= ($var === 'cost' || $var === 'cost-improvement')? 'active' : '' ?> <?= $size; ?>"><span class="ltriangle"></span><a href="<?= evaluation_url('cost', $id); ?>" class="">Cost Approach  <span class="fa fa-caret-down" aria-hidden="true"></span></a><span class="rtriangle"></span>
                <div class="inner_drp">
                    <a href="<?= evaluation_url('cost', $id); ?>" class="<?= ($var === 'cost') ? 'active' : '' ?>">Cost Approach-Land</a>
                    <a href="<?= evaluation_url('cost-improvement', $id); ?>" class="<?= ($var === 'cost-improvement') ? 'active' : '' ?>">Cost Approach-Improvements</a>
                </div>
            </li>
        <?php endif; ?>

        <li class="<?= ($var === 'exhibits')? 'active' : '' ?> <?= $size; ?>"><span class="ltriangle"></span><a href="<?= evaluation_url('exhibits', $id); ?>">Exhibits</a><span class="rtriangle"></span></li>
        <li class="<?= ($var === 'review')? 'active' : '' ?> <?= $size; ?>"><span class="ltriangle"></span><a href="<?= evaluation_url('review', $id); ?>">Review</a></li>
    </ul>
</div>
<div id="modal-save-changes" class="modal">
    <div class="modal-content WarningPopup">
            <div class="row">
                <div class="col s12">
                    <i class="material-icons dp48 modal-action modal-close modal-close-hyperlink">close</i>
                </div>
            </div>

            <div class="row">
                <div class="col s12">
                    <p class="title">Save changes</p>
                </div>
            </div>

            <div class="row">
                <div class="col s12">
                    <p>Do you want to save your changes?</p>
                </div>
            </div>
            <div class="row">
                <div class="col s12">
                    <button type="button" class="modal-action modal-close btn btn-primary btn_link" id="save_changes">Yes</button>
                    <button type="button" class="modal-action modal-close btn btn-primary btn_link" id="continue_without_saving">No</button>
                </div>
            </div>
    </div>
</div>
