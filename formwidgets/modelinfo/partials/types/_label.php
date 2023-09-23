<?php if ($data['mode'] == 'label') : ?>
    <div>
        <p><?php if($label = $data['label'] ?? null) : ?><?= $label ?>&nbsp;:&nbsp;<?php endif ?><span class="t-nw br-p-s20"><b><?= $data['value'] ?></b></p>
    </div>
<?php else : ?>
    <div>
        <p><?php if($label = $data['label'] ?? null) : ?><?= $label ?>&nbsp;:&nbsp;<?php endif ?></p><?= $data['value'] ?>
    </div>
<?php endif ?>