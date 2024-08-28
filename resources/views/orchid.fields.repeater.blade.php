<!-- resources/views/orchid/fields/repeater.blade.php -->

<div class="repeater">
    @foreach(old($name, $value ?? []) as $item)
        <div class="repeater-item">
            @foreach($fields as $field)
                {!! $field !!}
            @endforeach
            <button type="button" class="remove-repeater-item btn btn-danger">Remove</button>
        </div>
    @endforeach
    <button type="button" class="add-repeater-item btn btn-primary">Add</button>
</div>
