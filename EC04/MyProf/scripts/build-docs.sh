#!/usr/bin/env bash
set -euo pipefail

if [[ "$(uname -s)" == "Linux" ]]; then
  sudo apt-get update
  sudo apt-get install -y \
    libcairo2 \
    libpango-1.0-0 \
    libpangocairo-1.0-0 \
    libgdk-pixbuf2.0-0 \
    libffi-dev \
    shared-mime-info
fi

python -m pip install --upgrade pip
python -m pip install mkdocs mkdocs-material pymdown-extensions mkdocs-with-pdf
mkdocs build --strict
