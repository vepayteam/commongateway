<?php
/**
 * @var \app\services\payment\banks\bank_adapter_responses\CreatePayResponse $createPayResponse
 */

?>
<h2 style="margin-left: 30px;">Ожидается ответ от банка...</h2>
<div id="frame3ds" class="BankFrame" style="height: 600px; display: none">
    <form id="form3ds" action="<?=$createPayResponse->url?>" method='POST'>
        <input type="hidden" id="creq3ds" name="creq" value="<?=$createPayResponse->creq?>">
    </form>
</div>

<script>
    document.getElementById('form3ds').submit();
</script>
