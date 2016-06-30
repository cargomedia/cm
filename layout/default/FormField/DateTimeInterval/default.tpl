{select class="year" name="{$name}[year]" optionList=$years placeholder={translate 'Year'} selectedValue=$yy}
{select class="month" name="{$name}[month]" optionList=$months placeholder={translate 'Month'} selectedValue=$mm translate=true translatePrefix='.date.month.'}
{select class="day" name="{$name}[day]" optionList=$days placeholder={translate 'Day'} selectedValue=$dd}
{tag class="textinput start" el="input" name="{$name}[start]" id=$inputId type="text" value=$value placeholder=$placeholderStart}
{tag class="textinput end" el="input" name="{$name}[end]" id=$inputId type="text" value=$value placeholder=$placeholderEnd}
