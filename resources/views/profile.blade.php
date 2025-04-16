<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $user['nickname'] }} (@{{ $user['uniqueId'] }}) | TikTok Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background-color: #000000;
            padding: 1rem;
        }
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #fe2c55;
        }
        .logo i {
            margin-right: 5px;
        }
        .header-search {
            max-width: 300px;
        }
        .profile-header {
            background-color: white;
            border-radius: 10px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
        }
        .profile-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        .stat-item {
            text-align: center;
            min-width: 80px;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .video-card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            background-color: white;
            transition: transform 0.3s;
        }
        .video-card:hover {
            transform: translateY(-5px);
        }
        .video-thumbnail {
            position: relative;
            padding-top: 177.78%; /* 16:9 aspect ratio */
            overflow: hidden;
            background-color: #eee;
        }
        .video-thumbnail img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .video-duration {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        .video-info {
            padding: 1rem;
        }
        .video-title {
            font-size: 1rem;
            margin-bottom: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .video-stats {
            display: flex;
            gap: 1rem;
            color: #666;
            font-size: 0.9rem;
        }
        .load-more {
            display: block;
            width: 200px;
            margin: 0 auto 2rem;
            padding: 0.7rem 0;
            background-color: #fe2c55;
            color: white;
            border: none;
            border-radius: 50px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .load-more:hover {
            background-color: #e6254d;
        }
        .load-more:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .verified-badge {
            color: #20d5ec;
            margin-left: 5px;
        }
        footer {
            background-color: #f1f1f1;
            padding: 2rem 0;
            text-align: center;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand logo" href="{{ route('home') }}">
                <i class="fab fa-tiktok"></i> TikTok Viewer
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">Home</a>
                    </li>
                </ul>
                <form class="d-flex header-search" action="{{ route('search') }}" method="POST">
                    @csrf
                    <input class="form-control me-2" type="search" name="username" placeholder="TikTok Username (e.g., @tiktok)">
                    <button class="btn btn-danger" type="submit">Go</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        @if(isset($error))
            <div class="alert alert-warning my-3">
                {{ $error }}
            </div>
        @endif
        
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="row">
                <div class="col-md-auto text-center mb-3 mb-md-0">
                    <img src="{{ $user['avatarLarger'] }}" alt="{{ $user['nickname'] }}" class="profile-img">
                </div>
                <div class="col">
                    <h1>
                        {{ $user['nickname'] }}
                        @if($user['verified'])
                            <i class="fas fa-check-circle verified-badge"></i>
                        @endif
                    </h1>
                    <p class="text-muted">@{{ $user['uniqueId'] }}</p>
                    
                    @if(!empty($user['signature']))
                        <p>{{ $user['signature'] }}</p>
                    @endif
                    
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-value">{{ number_format($stats['followingCount']) }}</div>
                            <div class="stat-label">Following</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">{{ number_format($stats['followerCount']) }}</div>
                            <div class="stat-label">Followers</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">{{ number_format($stats['heartCount']) }}</div>
                            <div class="stat-label">Likes</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">{{ number_format($stats['videoCount']) }}</div>
                            <div class="stat-label">Videos</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Videos Section -->
        <h2 class="mb-4">Videos</h2>
        <div class="video-grid" id="video-container">
            @foreach($videos as $video)
                <div class="video-card">
                    <a href="{{ $video['play'] }}" target="_blank">
                        <div class="video-thumbnail">
                            <img src="{{ $video['cover'] }}" alt="{{ $video['title'] }}">
                            <div class="video-duration">{{ gmdate("i:s", $video['duration']) }}</div>
                        </div>
                    </a>
                    <div class="video-info">
                        <h3 class="video-title">{{ $video['title'] }}</h3>
                        <div class="video-stats">
                            <span><i class="fas fa-eye"></i> {{ number_format($video['play_count']) }}</span>
                            <span><i class="fas fa-heart"></i> {{ number_format($video['digg_count']) }}</span>
                            <span><i class="fas fa-comment"></i> {{ number_format($video['comment_count']) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($hasMore)
            <button id="load-more-btn" class="load-more" data-cursor="{{ $cursor }}" data-username="{{ $username }}">
                Load More
            </button>
        @endif
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>TikTok Viewer is not affiliated with TikTok. This is a third-party application.</p>
            <p>&copy; {{ date('Y') }} TikTok Viewer. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#load-more-btn').click(function() {
                const btn = $(this);
                const cursor = btn.data('cursor');
                const username = btn.data('username');
                
                btn.prop('disabled', true).text('Loading...');
                
                $.ajax({
                    url: '{{ route("load.more") }}',
                    type: 'POST',
                    data: {
                        username: username,
                        cursor: cursor,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.videos && response.videos.length > 0) {
                            let html = '';
                            
                            response.videos.forEach(function(video) {
                                const duration = formatDuration(video.duration);
                                
                                html += `
                                <div class="video-card">
                                    <a href="${video.play}" target="_blank">
                                        <div class="video-thumbnail">
                                            <img src="${video.cover}" alt="${video.title}">
                                            <div class="video-duration">${duration}</div>
                                        </div>
                                    </a>
                                    <div class="video-info">
                                        <h3 class="video-title">${video.title}</h3>
                                        <div class="video-stats">
                                            <span><i class="fas fa-eye"></i> ${formatNumber(video.play_count)}</span>
                                            <span><i class="fas fa-heart"></i> ${formatNumber(video.digg_count)}</span>
                                            <span><i class="fas fa-comment"></i> ${formatNumber(video.comment_count)}</span>
                                        </div>
                                    </div>
                                </div>
                                `;
                            });
                            
                            $('#video-container').append(html);
                            
                            if (response.hasMore) {
                                btn.data('cursor', response.cursor);
                                btn.prop('disabled', false).text('Load More');
                            } else {
                                btn.remove();
                            }
                        } else {
                            if (response.error) {
                                $('<div class="alert alert-warning my-3">' + response.error + '</div>').insertBefore(btn);
                            }
                            btn.remove();
                        }
                    },
                    error: function(xhr, status, error) {
                        $('<div class="alert alert-danger my-3">Failed to load videos: ' + error + '</div>').insertBefore(btn);
                        btn.prop('disabled', false).text('Try again');
                    }
                });
            });
            
            function formatDuration(seconds) {
                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = seconds % 60;
                return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
            }
            
            function formatNumber(num) {
                return new Intl.NumberFormat().format(num);
            }
        });
    </script>
</body>
</html> 