{select class="year" name="{$name}[year]" optionList=$years placeholder={translate 'Year'} selectedValue=$yy}
{select class="month" name="{$name}[month]" optionList=$months placeholder={translate 'Month'} selectedValue=$mm translate=true translatePrefix='.date.month.'}
{select class="day" name="{$name}[day]" optionList=$days placeholder={translate 'Day'} selectedValue=$dd}
