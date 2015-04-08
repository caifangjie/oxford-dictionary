<?php
class Check
{
	public function checkUser($username)
	{
		if(mb_strlen($username) > 32)
		{
			return mb_substr($username, 0,32);
		}
		else
		{
			return $username;
		}
	}

	public function checkAge($age)
	{
		if( $age >=15 && $age <= 18 )
		{
			return array('age'=>$age, 'start_age'=>15, 'end_age'=>18);
		}
		else if($age >=19  && $age <=23 )
		{
			return array('age'=>$age, 'start_age'=>19, 'end_age'=>23);
		}
		else if($age >= 24 && $age <= 28)
		{
			return array('age'=>$age, 'start_age'=>24, 'end_age'=>28);
		}
		else if($age >= 29 && $age <=35)
		{
			return array('age'=>$age, 'start_age'=>36, 'end_age'=>50);
		}
		else
		{
			return array('age'=>$age, 'start_age'=>15, 'end_age'=>18);
		}
	}

	public function checkQq($qq)
	{
		if(strlen($qq) > 11)
		{
			return substr($qq, 0,11);
		}
		else
		{
			return $qq;
		}
	}

	public function checkIntro($intro)
	{
		$string='';
		$len = strlen($intro);
		$true_len=0;
			for($i=0; $i<$len; $i++)
			{
				if($true_len <140)
				{
					if(ord(substr($intro, $i, 1))>0xa0)
					{
						$string .= substr($intro, $i, 3);//因为是以UTF-8编码保存的
						$i++;$i++;
						$true_len++;
					}
					else
					{
						$string .= $intro[$i];
						$true_len++;
					}
				}
			}
			return $string;
	}
}

?>