<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

abstract class BaseRequests extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     * 
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the error messages for the defined validation rules
     * 
     * @return array
     */
    public function messages()
    {
        return [
            "required" => "The field :attribute is required",
            "numeric" => "The field :attribute must be numeric"
        ];
    }

    /**
     * Overwrites the default error response format
     * 
     * @param Validator $validator
     * @throws HttpResponseException
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(
            [
                'message' => 'Was not possible to process the data passed',
                'errors' => $validator->errors()->all()
            ],
            Response::HTTP_UNPROCESSABLE_ENTITY
        ));
    }
}