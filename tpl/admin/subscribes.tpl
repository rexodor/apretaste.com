<!--{ vars }-->
{= path: "index.php?path=admin&page=subscribes" =}
{= title: Subscribes =}

<!--{ page -->
{{page				
	?$subscribes
		<table width="100%">
			<tr><th>ID</th><th>User</th><th>Phrase</th><th>Post date</th><th>Last alert</th></tr>
		[$subscribes]
			<tr><td align="center">{$id}</td>
			<td align="center"><a href="mailto:{$email}">{$email}</a></td>
			<td align="center">{$phrase}</td>
			<td align="center">{$moment}</td>
			<td align="center">?$last_alert {$last_alert} $last_alert? </td>
			</tr>
		[/$subscribes]
		</table>
	$subscribes?
page}}

{% layout %}