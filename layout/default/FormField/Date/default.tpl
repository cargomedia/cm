{select name="{$name}[year]" class={$class} optionList=$years placeholder={translate 'Year'} selectedValue=$yy}
{select name="{$name}[month]" class={$class} optionList=$months placeholder={translate 'Month'} selectedValue=$mm translate=true translatePrefix='.date.month.'}
{select name="{$name}[day]" class={$class} optionList=$days placeholder={translate 'Day'} selectedValue=$dd}
