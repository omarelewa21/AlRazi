<x-mail::message>
### Dear {{ $diagnoseUser->user->name }}<br><br><br>

{{ $diagnoseUser->referredBy->name }} has shared a diagnosis with a {{ $diagnoseUser->priority }} priority.<br><br><br>

### Patient Details:
* Name: {{ $diagnoseUser->diagnose->patient->name }}
* Age: {{ $diagnoseUser->diagnose->patient->age }}
* Gender: {{ $diagnoseUser->diagnose->patient->gender }}<br><br><br>


You can view the case by clicking the link below:
<x-mail::button :url="$url">
    View Diagnose
</x-mail::button>

Thanks<br>
{{ config('app.name') }}
</x-mail::message>
