<div>
    <?php if ($data['mode'] == 'label') : ?>
        <div class="mi_label">
            <?php if ($label = $data['label'] ?? 'null') : ?><?= $label ?>&nbsp;: <?php endif ?>
        </div>
        <div class="mi_value">
            <?= $data['value'] ?>
        </div>
    <?php else : ?>
        <div class="mi_raw">
            <?php if ($label = $data['label'] ?? null) : ?><p><?= $label ?>&nbsp;: </p><?php endif ?><?= $data['value'] ?>
        </div>
    <?php endif ?>
</div>