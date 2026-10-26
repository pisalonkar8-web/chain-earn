@if ($errors->any())
<div class="error-box" role="alert"><strong>Please check your details.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
