<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notificationTitle }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            border-bottom: 3px solid #4F46E5;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #4F46E5;
            font-size: 24px;
        }
        .content {
            margin-bottom: 30px;
        }
        .content p {
            margin: 10px 0;
            font-size: 16px;
        }
        .record-details {
            background-color: #f9fafb;
            border-left: 4px solid #4F46E5;
            padding: 15px;
            margin: 20px 0;
        }
        .record-details h2 {
            margin-top: 0;
            color: #374151;
            font-size: 18px;
        }
        .field {
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .field:last-child {
            border-bottom: none;
        }
        .field-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 14px;
            display: inline-block;
            min-width: 120px;
        }
        .field-value {
            color: #111827;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4F46E5;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #4338CA;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
        .automation-info {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 10px 15px;
            margin: 15px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 {{ $notificationTitle }}</h1>
        </div>
        
        <div class="content">
            <p>{{ $notificationBody }}</p>
        </div>
        
        <div class="automation-info">
            <strong>Automation:</strong> {{ $automation->name }}
        </div>
        
        <div class="record-details">
            <h2>📋 Record Details</h2>
            @foreach($recordData as $field)
                <div class="field">
                    <span class="field-label">{{ $field['label'] }}:</span>
                    <span class="field-value">{{ $field['value'] }}</span>
                </div>
            @endforeach
        </div>
        
        <a href="{{ config('app.url') }}/workspaces/{{ $automation->workspace_id }}/collections/{{ $record->collection_id }}/records/{{ $record->id }}" class="button">
            View Record
        </a>
        
        <div class="footer">
            <p>This is an automated notification from {{ config('app.name') }}</p>
            <p>Automation: {{ $automation->name }} | Collection: {{ $record->collection->name }}</p>
        </div>
    </div>
</body>
</html>
