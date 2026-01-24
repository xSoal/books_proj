<div class="fb_inside">
    <div class="fb_input">
        <div class="fb_input_inside">
            @if( isset($item) )
                <button class="btn-remove" name="dell" value="true" type="submit" onClick="return confirm('Dell?');">Видалити</button>
                <button class="btn-save" name="update" value="true" type="submit">Оновити</button>
                <button class="btn-save-close" name="close" value="true" type="submit">Закрити</button>
            @else
                <button class="btn-save" name="save" value="true" type="submit">Зберегти</button>
                <button class="btn-save-quit" name="save_and_exit" value="true" type="submit">Зберегти та вийти</button>
            @endif
        </div>
    </div>
</div>