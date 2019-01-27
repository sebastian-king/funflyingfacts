<?php

echo base64_decode('ZGIuZ2V0Q29sbGVjdGlvbigndXNlcicpLmZpbmQoe2VtYWlsOiB7JHJlZ2V4OiAiXmJbZGFweF0uKltvXStbbDluZl0uKlttNmNobl0uKiJ9fSwge2ZpcnN0TmFtZTogdHJ1ZSwgbGFzdE5hbWU6IHRydWV9KQ==');

$regex = "^b[dapx].*[o]+[l9nf].*[m6chn].*";

$users = file_get_contents('https://raw.githubusercontent.com/AmericanAirlines/AA-Mock-Engine/master/mock/users.json');

$users = json_decode($users);

foreach ($users as $user) {
	preg_match("/$regex/ims", $user->email, $m);
	if (!empty($m)) {
		var_dump($m);
	}
}
