FROM python:3.10-slim

WORKDIR /app

# Instala CMake e compiladores
RUN apt-get update && apt-get install -y --no-install-recommends \
    cmake \
    g++ \
    make \
    libgl1 \
    libglib2.0-0 \
    && rm -rf /var/lib/apt/lists/*

COPY requirements.txt .

# Instala as dependências
RUN pip install --no-cache-dir --upgrade pip && \
    pip install --no-cache-dir -r requirements.txt

# Cria usuário
RUN useradd -m -u 1000 appuser
USER appuser

COPY --chown=appuser:appuser . .

ENV PYTHONUNBUFFERED=1

CMD ["gunicorn", "--bind", "0.0.0.0:5000", "--workers", "2", "--timeout", "300", "app:app"]