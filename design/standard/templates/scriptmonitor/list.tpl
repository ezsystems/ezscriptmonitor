<div class="context-block">

<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

    <h1 class="context-title">{'Script monitor'|i18n( 'ezscriptmonitor' )}</h1>
    <div class="header-mainline"></div>

</div></div></div></div></div></div>

<div class="box-ml"><div class="box-mr"><div class="box-content">

    <div class="context-attributes">
        <div class="object">
            <p>{'These are the pending, currently executing or recently finished scripts.'|i18n( 'ezscriptmonitor' )}</p>
        </div>
    </div>

</div></div></div>

<div class="controlbar"><div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">

    <div class="block">
        <input type="submit" value="{'Refresh page'|i18n( 'ezscriptmonitor' )}" name="Refresh" class="button" onclick="javascript:location.reload(true)" />
    </div>

</div></div></div></div></div></div></div>

</div>


<div class="context-block">

<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

<h2 class="context-title">{'Current scripts'|i18n( 'ezscriptmonitor' )}</h2>

<div class="header-subline"></div>

</div></div></div></div></div></div>

<div class="box-ml"><div class="box-mr"><div class="box-content">

<table class="list" cellspacing="0"><tbody>

<tr>
    <th class="tight">{'Script ID'|i18n( 'ezscriptmonitor' )}</th>
    <th>{'Name'|i18n( 'ezscriptmonitor' )}</th>
    <th>{'Scheduled by'|i18n( 'ezscriptmonitor' )}</th>
    <th>{'Status'|i18n( 'ezscriptmonitor' )}</th>
    <th>{'Last report'|i18n( 'ezscriptmonitor' )}</th>
    <th class="tight">{'Progress'|i18n( 'ezscriptmonitor' )}</th>
</tr>

{def $user = false}
{foreach $scripts as $script sequence array( 'bglight', 'bgdark' ) as $seq}
<tr class="{$seq}">
    <td>{$script.id}</td>
    <td><a href={concat('/scriptmonitor/view/',$script.id)|ezurl}>{$script.name}</a></td>

    {set $user = fetch( content, object, hash( object_id, $script.user_id ) )}
    <td><a href={$user.main_node.url_alias|ezurl}>{$user.name}</a></td>

    {switch match = $script.status_text}
    {case match = 'not_started'}
        <td>{'Not started'|i18n( 'ezscriptmonitor' )}</td>
    {/case}
    {case match = 'active'}
        <td style="color: #7ed376;">{'Active'|i18n( 'ezscriptmonitor' )}</td>
    {/case}
    {case match = 'delayed'}
        <td style="color: #d9d978;">{'Waiting for  update'|i18n( 'ezscriptmonitor' )}</td>
    {/case}
    {case match = 'dead'}
        <td style="color: #d98078;">{'Dead'|i18n( 'ezscriptmonitor' )}</td>
    {/case}
    {case match = 'complete'}
        <td style="color: #a9a9a9;">{'Complete'|i18n( 'ezscriptmonitor' )}</td>
    {/case}
    {case}
        <td>{'Unknown'|i18n( 'ezscriptmonitor' )}</td>
    {/case}
    {/switch}

    <td>{$script.last_report_timestamp|l10n('time')}</td>
    <td>{$script.progress_text}</td>
</tr>
{/foreach}

</tbody></table>

</div></div></div>

<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-bl"><div class="box-br"><div class="box-content">
</div></div></div></div></div></div>

</div>
