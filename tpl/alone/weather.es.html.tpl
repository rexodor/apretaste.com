{= i18n_days: {
	SATURDAY: 'S&aacute;bado',
	SUNDAY: 'Domingo',
	MONDAY: 'Lunes',
	TUESDAY: 'Martes',
	WEDNESDAY: 'Mi&eacute;rcoles',
	THURSDAY: 'Jueves',
	FRIDAY: 'Viernes'	
} =}

?$provincias
	[$provincias]
		   
		<table>
			<tr>
				<td valign="top">
					<img src="{$weather_now.weatherIcon}" /><br/>
					{$i18n.{$weather_now.weatherCode}} 
				</td>
				<td style="border-left: 1px solid gray;" valign="top">
					<h2>{$locality}, {$weather_now.weatherTime}</h2>
					<br/>
					<strong>Temperatura:</strong> {$weather_now.weatherTemp} {$splitter}
					<strong>Viento: </strong> {$weather_now.windSpeed} {$splitter}
					<strong>Lluvias:</strong> {$weather_now.precipitation} {$splitter}
					<strong>Humedad:</strong> {$weather_now.humidity}<br/>
					<strong>Visibilidad:</strong> {$weather_now.visibility} {$splitter}
					<strong>Presi&oacute;n:</strong> {$weather_now.pressure} {$splitter}
					<strong>Nubosidad:</strong> {$weather_now.cloudcover} {$splitter}
				</td>
			</tr>
		</table>
		<table>
			<tr>
			[$weather_forecast]

				<td valign="top">
					<table>
						<tr><td colspan="3"><b>{$i18n_days.{$weatherDay}}, {$weatherDate:3,3}</b></td></tr>
						<tr>
							<td valign="top">
							<img src="{$weatherIcon}" />
							</td>
							<td valign="top">
								<span class="">{$i18n.{$weatherCode}}</span><br/>
								<span class="">Viento: <b>{$windDirection}</b> a <b>{$windSpeed}</span><br/>
							</td>
							<td valign="top">
								<span style="color: red;">{$tempMax}</span><br/>
								<span style="color: blue;">{$tempMin}</span>
							</td>
						</tr>
					</table>
				</td>
			[/$weather_forecast]
			</tr>
		</table>
		<hr/>
	[/$provincias]
$provincias?

?$satelite
	<h2 style="{$font}">Imagen del Sat&eacute;lite - WSI Corporation</h2>
	<img src="cid:wsi" width="700"><br/>
$satelite?

?$nasa
	<h2 style="{$font}">Imagen del Sat&eacute;lite de la NASA - GOES Project Science</h2>
	<img src="cid:goes" width="700"><br/>
$nasa?

?$radar
	<h2 style="{$font}">Imagen del radar</h2>
	<img src="cid:radar">
	<p style="color:gray;">NOTA: Esta imagen es animada (GIF) y en algunos clientes de correo electr&oacute;nico no se visualiza bien como es el caso de Outlook 2010.</p>
$radar?

?$mapa
	<h2 style="{$font}">Mapa de Presi&oacute;n Superficial</h2>
	<img src="cid:pronostico"><br/>
$mapa?
<br/>
<p>
<a style="{$font}" href = "mailto:{$reply_to}?subject=CLIMA satelite">Imagen del sat&eacute;lite</a> {$splitter} 
<a style="{$font}" href = "mailto:{$reply_to}?subject=CLIMA nasa">Imagen de la NASA </a> {$splitter}
<a style="{$font}" href = "mailto:{$reply_to}?subject=CLIMA radar">Imagen del radar</a> {$splitter} 
<a style="{$font}" href = "mailto:{$reply_to}?subject=CLIMA mapa">Mapa de presi&oacute;n superficial</a>
</p>
<hr/>
