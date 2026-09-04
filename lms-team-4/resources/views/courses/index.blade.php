<h1>Daftar Mata Kuliah</h1>
<ul>
    @foreach ($courses as $course)
        <li>
            <a href="{{ route('courses.show', $course['id']) }}">
                {{ $course['code'] }} - {{ $course['name'] }} ({{ $course['sks'] }} SKS)
            </a>
        </li>
    @endforeach
</ul>