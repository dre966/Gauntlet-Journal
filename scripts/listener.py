import websockets, asyncio, subprocess
async def handle(websocket):
    async for message in websocket:
        a = subprocess.Popen(f"python {message}")
        await websocket.send(message)

async def main():
    async with websockets.serve(handle, "localhost", 80):
        await asyncio.Future()

asyncio.run(main())
