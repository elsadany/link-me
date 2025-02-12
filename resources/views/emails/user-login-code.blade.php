

@component('mail::message')

    <p>{{ __('من فضلك أستخدم الكود التالى لاستكمال الطلب الخاص بك') }}</p>
    <br>
    <p style="text-align: center; margin: auto; padding: 8px; background-color: rgb(91, 144, 251); color: #FFFF; font-weight: bold;">{{$code}}</p>
    <br>

@endcomponent


