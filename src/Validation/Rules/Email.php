<?php

namespace LightWeight\Validation\Rules;

use LightWeight\Validation\Contracts\ValidationRuleContract;

class Email implements ValidationRuleContract
{
    /**
     *
     * @param string $field
     * @param array $data
     * @return bool
     */
    public function isValid(string $field, array $data): bool
    {
        if(!array_key_exists($field, $data)) {
            return false;
        }
        if(empty($data[$field])) {
            return false;
        }
        $email = strtolower(trim($data[$field]));

        $split = explode('@', $email);

        if (count($split) != 2) {
            return false;
        }

        [$username, $domain] = $split;

        $split = explode('.', $domain);

        if (count($split) != 2) {
            return false;
        }

        [$label, $topLevelDomain] = $split;

        return strlen($username) >= 1
            && strlen($label) >= 1
            && strlen($topLevelDomain) >= 1;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return 'Email has invalid format';
    }
}
