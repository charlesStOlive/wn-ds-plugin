<ul style="line-height:1.4em">
    <div data-attribute="w-collapse" data-default-lg="open">
        <div class="w-collapse__title field-section">
            <h4 style="text-transform: uppercase;color: #35425b;"><?= $data['label'] ?></h4>
            <span class="w-collapse__icon"></span>
        </div>
        <div class="w-collapse__content">
            <ul>
                <?php foreach ($data['data'] as $subData) : ?>
                    <li>
                        <?= $this->makePartial('types/label', ['data' => $subData]) ?>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>
    </div>
</ul>