<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VisitorReceiveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    public function getReferer()
    {
        return $this->headers->get('referer');
    }

    public function getVisitorCookie()
    {
        return $this->input('ksars');
    }

    public function getVisitorHash()
    {
        $ip = $this->ip();
        $userAgent = $this->userAgent();
        return MD5($ip.$userAgent);
    }

    public function getVisitHash()
    {
        $ip = $this->ip();
        $userAgent = $this->userAgent();
        return MD5($ip.time().$userAgent);
    }
}
