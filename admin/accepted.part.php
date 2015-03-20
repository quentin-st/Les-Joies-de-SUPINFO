<div class="gifsAdmin">
    <? foreach (getGifs(-1,GifState::ACCEPTED) as $gif) {
        ?>
        <div class="gifItemAdmin">
            <img src="<?= $gif->getGifUrl() ?>" alt="<?= $gif->catchPhrase ?>" />

            <div class="gifValidation">
                <div><span><textarea id="caption<? echo $gif->id ?>" type="text" rows="2"><?= $gif->catchPhrase ?></textarea></span></div>
                <div>
                    <span>
                        <button type="button" data-state="published" data-gifid="<? echo $gif->id ?>" class="btn btn-primary btnValidation">Publier</button>
                        <button type="button" data-state="refused" data-gifid="<? echo $gif->id ?>" class="btn btn-danger btnValidation">Rejeter</button>
                    </span>
                </div>
                <div><span><?= $gif->publishDate->format('d-m-Y') ?> par <?= $gif->submittedBy ?></span></div>
            </div>
        </div>
    <?
    } ?>
</div>
<script>
    $('.mRefused').removeClass('active');
    $('.mAccepted').addClass('active');
    $('.mSubmitted').removeClass('active');
</script>