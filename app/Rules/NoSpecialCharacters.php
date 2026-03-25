<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NoSpecialCharacters implements Rule
{
    protected $allowSpecial;

    protected $pattern = '/[!@#$%^&*()?":{}|<>\[\]\\\\\/\'`;+=~`_—✓©®™•¡¿§¤€£¥₩₹†‡‰¶∆∑∞µΩ≈≠≤≥÷±√°¢¡¬‽№☯☮☢☣♻⚡⚠✔️🔒🎉😊💡🌍🚀📦🧩🛠️🐍🔥💾📁🖥️⌨️🔧🔍]/u';

    public function __construct($allowSpecial = false)
    {
        $this->allowSpecial = $allowSpecial;
    }

    public function passes($attribute, $value)
    {
        if ($this->allowSpecial) {
            return true;
        }
        return !preg_match($this->pattern, $value);
    }

    public function message()
    {
        return 'The :attribute field contains special characters which are not allowed.';
    }
}
