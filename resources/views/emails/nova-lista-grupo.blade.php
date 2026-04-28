<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nova Lista no Grupo</title>
</head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #f3f4f6; padding: 20px; border-radius: 8px;">
        <h1 style="color: #1f2937;">Nova Lista: {{ $lista->nome }}</h1>
        
        <p style="color: #4b5563;">
            Uma nova lista foi criada no grupo <strong>{{ $grupo->nome }}</strong> por <strong>{{ $criador->name }}</strong>.
        </p>
        
        <div style="margin: 20px 0;">
            <a href="{{ route('listas.show', $lista->id) }}" 
               style="background: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">
                Ver Lista
            </a>
        </div>
        
        <p style="color: #9ca3af; font-size: 14px;">
            Você está receive esta mensagem porque é membro do grupo {{ $grupo->nome }}.
        </p>
    </div>
</body>
</html>