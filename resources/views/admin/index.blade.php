@extends('layouts.admin')

@section('content')
    <div class="main_section active main_page_admin">

        {{-- <header>
            <div style="display:flex; align-items:center; justify-content:space-between; max-width:1200px; margin:0 auto;">
                <div style="font-size:24px; font-weight:bold; display:flex; align-items:center; gap:10px;">
                    <i class="fa-solid fa-user-tie"></i> Адмін-панель
                </div>
            </div>
        </header> --}}
        
        
        <div class="container containter__mainPage">

            <div class="admin-stats-wrapper">
                <div class="stats-header">
                    {{-- <h1>Статистика активності</h1> --}}
                
                </div>

                <div class="stats-grid-counters" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px;">
    
                    <div class="card-panel" style="display: flex; align-items: center; gap: 20px; border-bottom: 4px solid #32a89b;">
                        <div style="background: rgba(50, 168, 155, 0.1); padding: 15px; border-radius: 8px;">
                            <i class="fa-solid fa-book" style="font-size: 24px; color: #32a89b;"></i>
                        </div>
                        <div>
                            <div style="font-size: 14px; color: #718096;">Всього книг</div>
                            <div style="font-size: 24px; font-weight: bold; color: #1a3352;">{{ number_format($statsCounts['books'], 0, '.', ' ') }}</div>
                        </div>
                    </div>
                
                    <div class="card-panel" style="display: flex; align-items: center; gap: 20px; border-bottom: 4px solid #4a90e2;">
                        <div style="background: rgba(74, 144, 226, 0.1); padding: 15px; border-radius: 8px;">
                            <i class="fa-solid fa-list-check" style="font-size: 24px; color: #4a90e2;"></i>
                        </div>
                        <div>
                            <div style="font-size: 14px; color: #718096;">Характеристик</div>
                            <div style="font-size: 24px; font-weight: bold; color: #1a3352;">{{ $statsCounts['characteristics'] }}</div>
                        </div>
                    </div>
                
                    <div class="card-panel" style="display: flex; align-items: center; gap: 20px; border-bottom: 4px solid #f6ad55;">
                        <div style="background: rgba(246, 173, 85, 0.1); padding: 15px; border-radius: 8px;">
                            <i class="fa-solid fa-tags" style="font-size: 24px; color: #f6ad55;"></i>
                        </div>
                        <div>
                            <div style="font-size: 14px; color: #718096;">Значення характеристик</div>
                            <div style="font-size: 24px; font-weight: bold; color: #1a3352;">{{ number_format($statsCounts['values'], 0, '.', ' ') }}</div>
                        </div>
                    </div>
                
                </div>
            
                <div class="stats-grid-top">
                    <div class="card-panel">
                        <div class="card-title">Динаміка за тиждень</div>
                        <div style="height: 300px;">
                            <canvas id="mainActivityChart"></canvas>
                        </div>
                    </div>
            
                    <div class="card-panel">
                        <div class="card-title">Найчастіші запити</div>
                        @foreach($topQueries as $q)
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <span style="color: #2d3748;">{{ $q->search_query }}</span>
                                <span class="count-pill">{{ $q->total }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>


            
                <div class="card-panel">
                    <div class="card-title">Останні дії користувачів</div>

                    <table class="activity-log-table">
                        <thead>
                            <tr>
                                <th>Дата и час</th>
                                <th>Тип дії</th>
                                <th>Запит / Книга</th>
                                <th>Застосовані фільтри</th>
                                <th>Результатів</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentActivities as $item)
                            <tr>
                                <td>{{ $item->created_at->format('d.m.Y H:i') }}</td>
                                
                                {{-- Тип действия: теперь будет и Поиск, и Просмотр --}}
                                <td>
                                    <span style="color: {{ $item->type == 'view' ? '#32a89b' : '#e53e3e' }}">
                                        {{ $item->type == 'view' ? 'Перегляд' : 'Пошук' }}
                                    </span>
                                </td>

                                {{-- Запрос или Название книги --}}
                                <td>
                                    @if($item->type == 'view' && $item->book)
                                        @php
                                            // 1. Пытаемся взять текущую локаль
                                            // 2. Если пусто - берем первую доступную
                                            $translation = $item->book->translates->firstWhere('locale', app()->getLocale()) 
                                                           ?? $item->book->translates->first();
                                        @endphp
                                
                                        @if($translation)
                                            <a href="{{ route('admin.viewBook', $item->book->id) }}")><strong>{{ $translation->name }}</strong></a>
                                        @else
                                            <span style="color: #94a3b8;">Назва відсутня (ID: {{ $item->book_id }})</span>
                                        @endif
                                    @else
                                        <em>{{ $item->search_query ?? '—' }}</em>
                                    @endif
                                </td>

                                {{-- Застосовані фільтри (Безопасный вывод) --}}
                                <td>
                                    @if($item->filters)
                                        @foreach($item->filters as $key => $val)
                                            <span class="badge-filter">
                                                <strong>{{ $key }}:</strong> 
                                                @if(is_array($val))
                                                    {{ json_encode($val, JSON_UNESCAPED_UNICODE) }}
                                                @else
                                                    {{ $val }}
                                                @endif
                                            </span>
                                        @endforeach
                                    @endif
                                </td>

                                <td>
                                    @if ($item->type === 'search')
                                    {{ $item->results_count }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <br>
            {{ $recentActivities->links() }}


        </div>
    </div>








    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Проверяем, что элемент существует, чтобы не было ошибок в консоли
        const canvas = document.getElementById('mainActivityChart');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartData['labels']) !!}, // Метки дат
                    datasets: [{
                        label: 'Перегляди книг',
                        data: {!! json_encode($chartData['views']) !!},
                        borderColor: '#32a89b', // Бирюзовый из твоего интерфейса
                        backgroundColor: 'rgba(50, 168, 155, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Пошукові запити',
                        data: {!! json_encode($chartData['searches']) !!},
                        borderColor: '#f67280', // Розовый
                        backgroundColor: 'rgba(246, 114, 128, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { 
                            beginAtZero: true,
                            ticks: { stepSize: 1 } // Только целые числа
                        }
                    },
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }
    </script>



@endsection


