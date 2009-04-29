<div class="context-block">

<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

    {if $script|is_object}
        <h1 class="context-title">{'Script monitor:'|i18n( 'ezscriptmonitor' )} {$script.name}</h1>
    {else}
        <h1 class="context-title">{'Script monitor:'|i18n( 'ezscriptmonitor' )} {'Script not found'|i18n( 'ezscriptmonitor' )}</h1>
    {/if}
    <div class="header-mainline"></div>

</div></div></div></div></div></div>

<div class="box-ml"><div class="box-mr"><div class="box-content">

{if $script|is_object}

    <table class="list" cellspacing="0"><tbody>

    <tr>
        <th>{'Data'|i18n( 'ezscriptmonitor' )}</th>
        <th>{'Value'|i18n( 'ezscriptmonitor' )}</th>
    </tr>

    <tr class="bglight">
        <td>{'Script ID'|i18n( 'ezscriptmonitor' )}</td>
        <td>{$script.id}</td>
    </tr>

    {* This is not ready yet
    <tr class="bgdark">
        <td>{'Process ID'|i18n( 'ezscriptmonitor' )}</td>
        <td>{$script.process_id_text}</td>
    </tr>
    *}

    <tr class="bgdark">
        <td>{'Name'|i18n( 'ezscriptmonitor' )}</td>
        <td>{$script.name}</td>
    </tr>

    <tr class="bglight">
        <td>{'Command'|i18n( 'ezscriptmonitor' )}</td>
        <td>{$script.command}</td>
    </tr>

    {def $user = fetch( content, object, hash( object_id, $script.user_id ) )}
    <tr class="bgdark">
        <td>{'Scheduled by'|i18n( 'ezscriptmonitor' )}</td>
        <td><a href={$user.main_node.url_alias|ezurl}>{$user.name}</a></td>
    </tr>

    <tr class="bglight">
        <td>{'Status'|i18n( 'ezscriptmonitor' )}</td>
        {switch match = $script.status_text}
        {case match = 'not_started'}
            <td>{'Not started'|i18n( 'ezscriptmonitor' )}</td>
        {/case}
        {case match = 'active'}
            <td style="color: #7ed376;">{'Active'|i18n( 'ezscriptmonitor' )}</td>
        {/case}
        {case match = 'waiting'}
            <td style="color: #d9d978;">{'Waiting for  update'|i18n( 'ezscriptmonitor' )}</td>
        {/case}
        {case match = 'dead'}
            <td style="color: #d98078;">{'Dead'|i18n( 'ezscriptmonitor' )}</td>
        {/case}
        {case match = 'complete'}
            <td style="color: #a9a9a9;">{'Complete'|i18n( 'ezscriptmonitor' )}</td>
        {/case}
        {default}
            <td>{'Unknown'|i18n( 'ezscriptmonitor' )}</td>
        {/default}
        {/switch}
    </tr>

    <tr class="bgdark">
        <td>{'Last report'|i18n( 'ezscriptmonitor' )}</td>
        <td>{$script.last_report_timestamp|l10n('time')}</td>
    </tr>

    <tr class="bglight">
        <td>{'Progress'|i18n( 'ezscriptmonitor' )}</td>
        <td>{$script.progress_text}</td>
    </tr>

    </tbody></table>

{else}

    <div class="context-attributes">
        <div class="object">
            <p>{'The script does not exist!'|i18n( 'ezscriptmonitor' )}</p>
        </div>
    </div>

{/if}

</div></div></div>

<div class="controlbar"><div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">

    <div class="block">
        <input type="submit" value="{'Refresh page'|i18n( 'ezscriptmonitor' )}" name="Refresh" class="button" onclick="javascript:location.reload(true)" />
    </div>

</div></div></div></div></div></div></div>

</div>
