@extends('layouts.arix', ['navbar' => 'tos', 'sideEditor' => true])

@section('title')
    Arix Terms of Service
@endsection

@section('content')

    <form action="{{ route('admin.arix.tos') }}" method="POST">
        <div class="header">
            <p>Terms of Service Editor</p>
            <span class="description-text">Edit the content displayed on the public /tos page. HTML is fully supported and rendered raw. Leave empty to hide the navbar link and return 404 on /tos.</span>
        </div>
        <div class="input-field">
            <label for="arix:tos_content">Terms of Service content</label>
            <textarea id="arix:tos_content" name="arix:tos_content" rows="20" class="w-full font-mono">{{ old('arix:tos_content', $tos_content ?? '') }}</textarea>
            <small>HTML allowed. Raw output on the public page. Use for legal terms, privacy policy, or server rules.</small>
        </div>
        <div class="floating-button">
            {!! csrf_field() !!}
            <button type="submit" class="button button-primary">Save changes</button>
        </div>
    </form>
@endsection
