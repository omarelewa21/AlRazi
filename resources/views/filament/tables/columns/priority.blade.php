<div>
    <select @change='$wire.priorityChanged({{$getRecord()->id}}, $el.value)' class="rounded-lg border border-gray-300 px-4 py-2">
        <option value="low">Low</option>
        <option value="medium">Medium</option>
        <option value="high">High</option>
    </select>
</div>
