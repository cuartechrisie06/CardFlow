@csrf
@if($card->exists) @method('PUT') @endif
<section class="dashboard-card">
    <div class="card-form-grid">
        <label class="field-group"><span>Card name</span><input name="title" value="{{ old('title',$card->title) }}" required></label>
        <label class="field-group"><span>Artist / group</span><input name="artist" value="{{ old('artist',$card->artist) }}" required></label>
        <label class="field-group"><span>Album / release</span><input name="album" value="{{ old('album',$card->album) }}"></label>
        <label class="field-group"><span>Card type</span><select name="variant_type" class="field-select">@foreach(['Album','Lucky draw','Broadcast','Season\'s greetings','Other'] as $type)<option value="{{ $type }}" @selected(old('variant_type',$card->variant_type)===$type)>{{ $type }}</option>@endforeach</select></label>
        <label class="field-group"><span>Image upload</span><input type="file" name="photo" accept="image/*"></label>
        <label class="field-group"><span>Notes</span><textarea name="notes" rows="4"></textarea></label>
    </div>
</section>
<div style="display:flex;gap:10px;justify-content:flex-end;"><a href="{{ route('admin.catalog.index') }}" class="dashboard-search-submit" style="text-decoration:none;">Cancel</a><button class="dashboard-add-card" style="border:0;">Save card</button></div>
