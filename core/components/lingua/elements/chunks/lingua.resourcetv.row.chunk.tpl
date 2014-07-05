
<!-- lingua's clone TV started with id: "[[+tv.id]]" -->
[[+tv.type:isnot=`hidden`:then=`
<div class="x-form-item x-tab-item modx-tv" id="tv[[+tv.id]]-tr">
    <label for="tv[[+tv.id]]" class="x-form-item-label modx-tv-label" style="width: auto;">
        <div class="modx-tv-label-title">
            [[+tv.showCheckbox:notempty=`<input type="checkbox" name="tv[[+tv.id]]-checkbox" class="modx-tv-checkbox" value="1" />`]]
            <span class="modx-tv-caption" id="tv[[+tv.id]]-caption">
                [[+tv.caption:default=`[[+tv.name]]`]]
            </span>
        </div>
        <a class="modx-tv-reset" id="modx-tv-reset-[[+tv.id]]" title="[[%set_to_default]]" style="float: left;"></a>
        [[+tv.description:isnotempty=`<span class="modx-tv-label-description">[[+tv.description]]</span>`]]
    </label>
    [[+tv.inherited:is=`1`:then=`<span class="modx-tv-inherited">[[%tv_value_inherited]]</span>`]]
    <div class="x-form-clear-left"></div>
    <div class="x-form-element modx-tv-form-element">
        <input type="hidden" id="tvdef[[+tv.id]]" value="[[+tv.default_text:escape]]" />
        [[+tv.formElement]]
    </div>
</div>
<script type="text/javascript">
    Ext.onReady(function() {
        new Ext.ToolTip({
            target: 'tv[[+tv.id]]-caption',
            html: '&#91;&#91;*[[+tv.name]]&#93;&#93;'
        });
    });
</script>
`:else=`
<input type="hidden" id="tvdef[[+tv.id]]" value="[[+tv.default_text:escape]]" />
[[+tv.formElement]]
`]]
<!-- lingua's clone TV ended with id: "[[+tv.id]]" -->
