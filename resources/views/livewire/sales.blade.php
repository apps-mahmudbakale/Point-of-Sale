<div>
@role('admin')
<x-sales :sales="$sales" />
@else
<x-user-sales :sales="$sales" />
@endrole
</div>
