{select name="{$name}[year]" optionList=$years placeholder={translate 'Year'} selectedValue=$yy}
{select name="{$name}[month]" optionList=$months placeholder={translate 'Month'} selectedValue=$mm translate=true translatePrefix='.date.month.'}
{select name="{$name}[day]" optionList=$days placeholder={translate 'Day'} selectedValue=$dd}
{tag el="input" name="{$name}[date]" type="date" value=$date class="textinput"}
