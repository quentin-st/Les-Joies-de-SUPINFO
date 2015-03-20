<div class="gifsAdmin">
    <? foreach (getGifs(-1,GifState::REFUSED) as $gif) {
        ?>
        <div class="gifItemAdmin">
            <img src="<?= $gif->getGifUrl() ?>" alt="<?= $gif->catchPhrase ?>" />

            <div class="gifValidation">
                <div><span><textarea id="caption<? echo $gif->id ?>" type="text" rows="2"><?= $gif->catchPhrase ?></textarea></span></div>
                <div>
                    <span>
                        <button type="button" data-state="accepted" data-gifid="<? echo $gif->id ?>" class="btn btn-success btnValidation">Valider</button>
                        <button type="button" data-state="deleted" data-gifid="<? echo $gif->id ?>" class="btn btn-warning btnValidation">Supprimer</button>
                    </span>
                </div>
                <div><span><?= $gif->publishDate->format('d-m-Y') ?> par <?= $gif->submittedBy ?></span></div>
            </div>
        </div>
    <?
    } ?>
</div>
<script>
    $('.mRefused').addClass('active');
    $('.mAccepted').removeClass('active');
    $('.mSubmitted').removeClass('active');
</script>